<?php


namespace App\Services;

use App\Constants\SessionsConstants;
use App\Models\PosSession;
use Illuminate\Support\Facades\DB;

class PosSessionService
{
    public static function getAll($userCompanyId, $options)
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $searchDefault = '';

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;
        $posTerminalId = $options['posTerminalId'] ?? null;

        $sessions = PosSession::query()->with(['posTerminal','openedByUser','closedByUser']);

        $sessions->where('company_id', $userCompanyId);
        $sessions->filter($search);
        if($posTerminalId)
        {
            $sessions->where('pos_terminal_id', $posTerminalId);

        }



        if ($isPaginated) {
            $sessions = $sessions->paginate($perPage);
        } else {
            $sessions = $sessions->get();
        }

        return $sessions;

    }

    public  function generateSessionNumber($companyId)
    {
        $currentYear = date('y');
        $latestSession = PosSession::where('company_id', $companyId)->whereNotNull('session_number')->withTrashed()->latest()->first();

        if ($latestSession) {
            if (str_contains($latestSession->session_number, SessionsConstants::NUMBER_SEPARATOR)) {
                $lastNumber = (int)substr($latestSession->session_number, 4);
            } else {
                $lastNumber = (int)substr($latestSession->session_number, 3);
            }

            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return SessionsConstants::NUMBER_PREFIX . $currentYear . str_pad($newNumber, SessionsConstants::NUMBER_MIN_LENGTH, SessionsConstants::NUMBER_PAD_STR, STR_PAD_LEFT);
    }

    public function open(array $data): PosSession
    {
        return DB::transaction(function () use ($data) {

            // 1️⃣ منع فتح أكثر من Session لنفس Terminal
            $alreadyOpen = PosSession::where('pos_terminal_id', $data['pos_terminal_id'])
                ->where('status', 'open')
                ->exists();

            if ($alreadyOpen) {
                throw new \Exception('There is already an open session for this terminal.');
            }
         $user=auth()->user();
            $data['company_id']=$user->company_id;
            $data['opened_by_user_id']=$user->id;
            $data['session_number']=$this->generateSessionNumber($user->company_id);

            // 2️⃣ إنشاء Session
            return PosSession::create([
                ...$data,
                'status' => 'open',
                'opening_date' => now(),
            ]);
        });
    }

    public function close(PosSession $session, int $closedByUserId): PosSession
    {
        if ($session->status !== 'OPEN') {
            throw new \Exception('Session is not open.');
        }

        $session->update([
            'status' => 'closed',
            'closed_by_user_id' => $closedByUserId,
            'closing_date' => now(),
        ]);

        return $session->refresh();
    }

    public function update(PosSession $session, array $data): PosSession
    {
        $session->update($data);

        return $session->refresh();
    }

    public function delete(PosSession $session): void
    {
        if ($session->cashTrays()->exists()) {
            throw new \Exception('Cannot delete session with cash trays.');
        }

        $session->delete();
    }
    public static function getOpenSessionForTerminal(int $companyId, int $terminalId): ?PosSession
    {
        return PosSession::query()
            ->where('company_id', $companyId)
            ->where('pos_terminal_id', $terminalId)
            ->where('status', 'OPEN')
            ->latest('opening_date')
            ->first();
    }
}
