<?php
namespace App\Services;


use App\Models\PosCashTray;
use App\Models\PosSession;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Constants\CashTrayConstants;
class CashTrayService
{
    public static function getAll( $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $searchDefault = '';
        $onlyClosed = false;
        $onlyOpen = false;
        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;
        $onlyClosed = json_decode($options['onlyClosed'] ?? $onlyClosed);
        $onlyOpen= json_decode($options['onlyOpen'] ?? $onlyOpen);

        $trays = PosCashTray::query();


            $trays->where('company_id', $userCompanyId);


        $trays->filter($search);

        if($onlyClosed)
        {
            $trays = $trays->where('status', 'closed');
        }
        if($onlyOpen)
        {
            $trays = $trays->where('status', 'open');
        }
        if ($isPaginated) {
            $trays = $trays->paginate($perPage);
        } else {
            $trays = $trays->get();
        }

        return $trays;
    }

    public  function generateTrayNumber($companyId, $sessionId)
    {
        $session = PosSession::find($sessionId);
        if (!$session) {
            throw new \Exception("Session not found");
        }

        $latestTray = PosCashTray::where('company_id', $companyId)
            ->where('pos_session_id', $sessionId)
            ->whereNotNull('tray_number')
            ->latest()
            ->first();

        if ($latestTray) {
            // Extract numeric part after prefix (e.g., 'TR00001')
            $lastNumber = (int) substr($latestTray->tray_number, strlen(CashTrayConstants::NUMBER_PREFIX));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return CashTrayConstants::NUMBER_PREFIX
            . str_pad($newNumber, CashTrayConstants::NUMBER_MIN_LENGTH, CashTrayConstants::NUMBER_PAD_STR, STR_PAD_LEFT)
            . $session->session_number;
    }

    public function openTray(PosSession $session, array $data): PosCashTray
    {
        return DB::transaction(function () use ($session, $data) {

            $session->cashTrays()
                ->where('status', 'open')
                ->lockForUpdate()
                ->get();

            $openTrayExists = $session->cashTrays()
                ->where('status', 'open')
                ->exists();

            if ($openTrayExists) {
                throw new Exception('Previous cash tray must be closed before opening a new one.');
            }
            $user=auth()->user();
            $tray = PosCashTray::create([
                'company_id' => $session->company_id,
                'pos_session_id' => $session->id,
                'pos_terminal_id'=>$data['pos_terminal_id'],
                'tray_number'=>$this->generateTrayNumber($user->company_id,$session->id),
                'status' => 'open',
                'opened_at' => now(),
            ]);

            $tray->balances()->createMany(
                collect($data['opening_balances'])->map(function ($balance) {
                    return [
                        'currency_id' => $balance['currency_id'],
                        'opening_amount' => $balance['amount'],
                        'expected_amount' => $balance['amount'], // بالبداية المتوقع = الافتتاحي
                        'declared_closing_amount' => null,
                        'difference' => null,
                    ];
                })->toArray()
            );

            return $tray->load('balances');
        });
    }
    public function calculateExpectedBalanceByCurrency(PosCashTray $tray): array
    {
//        $payments = $tray->payments()
//            ->selectRaw('currency_id, SUM(CASE WHEN type="payment" THEN amount ELSE -amount END) as total')
//            ->groupBy('currency_id')
//            ->pluck('total','currency_id')
//            ->toArray();

        $result = [];

        foreach ($tray->balances as $balance) {

            $currencyId = $balance->currency_id;
            $opening = $balance->opening_amount;
            $movement = $payments[$currencyId] ?? 0;

            $expected = $opening + $movement;

            $balance->update([
                'expected_amount' => $expected
            ]);

            $result[$currencyId] = $expected;
        }

        return $result;
    }
    public function closeTray(PosCashTray $tray, array $countedBalances): PosCashTray
    {
        if ($tray->status !== 'open') {
            throw new Exception('Tray already closed.');
        }

        return DB::transaction(function () use ($tray, $countedBalances) {

            $expected = $this->calculateExpectedBalanceByCurrency($tray);

            foreach ($countedBalances as $cb) {

                $currencyId = $cb['currency_id'];
                $declared = $cb['amount'] ?? 0;
                $expectedAmount = $expected[$currencyId] ?? 0;

                $difference = $declared - $expectedAmount;

                $tray->balances()
                    ->where('currency_id', $currencyId)
                    ->update([
                        'declared_closing_amount' => $declared,
                        'difference' => $difference,
                    ]);

            }

            $tray->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            return $tray->load('balances');
        });
    }
    public function getOpenTrayBySession(int $sessionId): ?PosCashTray
    {

        return PosCashTray::where('pos_session_id', $sessionId)
            ->where('status', 'open')
            ->first();
    }
}
