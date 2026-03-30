<?php


namespace App\Services;

use App\Constants\PosConstants;
use App\Models\PosTerminal;
use Illuminate\Support\Facades\DB;

class PosTerminalService
{
    public function getAll($userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $searchDefault = '';


        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;


        $pos = PosTerminal::query()->with('warehouse');


        $pos->where('company_id', $userCompanyId);


        $pos->filter($search);


        if ($isPaginated) {
            $categories = $pos->paginate($perPage);
        } else {
            $categories = $pos->get();
        }

        return $categories;
    }

    public function generatePosNumber($companyId)
    {
        $currentYear = date('y');
        $latestPos = PosTerminal::where('company_id', $companyId)->withTrashed()->whereNotNull('pos_number')->latest()->first();

        if ($latestPos) {
            // Extract the numeric part after the prefix (e.g., POS001 → 1)
            preg_match('/\d+/', $latestPos->pos_number, $matches);
            $lastNumber = isset($matches[0]) ? (int)$matches[0] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }


        return PosConstants::NUMBER_PREFIX . str_pad($newNumber, PosConstants::NUMBER_MIN_LENGTH, PosConstants::NUMBER_PAD_STR, STR_PAD_LEFT);

    }

    public function store(array $data): PosTerminal
    {
        return DB::transaction(function () use ($data) {
            $companyId = auth()->user()->company_id;
            $data['company_id'] = $companyId;
            $data['pos_number'] = $this->generatePosNumber($companyId);
            return PosTerminal::create($data);
        });
    }

    public function update(PosTerminal $terminal, array $data): PosTerminal
    {
        return DB::transaction(function () use ($terminal, $data) {
            $terminal->update($data);

            return $terminal->refresh();

        });
    }

    public function delete(PosTerminal $terminal)
    {
        return DB::transaction(function () use ($terminal) {
            // Optional protection:
            if ($terminal->sessions()->exists()) {
                throw new \Exception('Cannot delete terminal with existing sessions.');
            }

            $terminal->delete();

        });
       }
}
