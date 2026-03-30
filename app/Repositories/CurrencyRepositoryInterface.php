<?php

namespace App\Repositories;

interface CurrencyRepositoryInterface
{
    public function getCodeById(int $id): string;
}
