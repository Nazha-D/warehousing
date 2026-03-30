<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    /**
     * @return mixed
     */
    public static function getAll( $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $nameDefault = '';

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $name = $options['search'] ?? $nameDefault;
        $salesmen=$options['salesmen'] ?? false;

        $users = User::active();
        $users->filter($name);
          if($salesmen)
        $users->where('is_salesperson','=',$salesmen);

            $users = $users->where('company_id', $userCompanyId)->filter($name);
            if ($isPaginated) {
                $users = $users->paginate($perPage);
            } else {
                $users = $users->get();
            }


        return $users;
    }
}
