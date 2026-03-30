<?php

namespace App\Helpers;

use Carbon\Carbon;

class GeneralHelper
{
    public static function changeDateFormatToYmd($date)
    {
        return $date != null ? Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d') : null;
    }

    public static function changeDateFormatTodmY($date)
    {
        return $date != null ? Carbon::createFromFormat('Y-m-d', $date->format('Y-m-d'))->format('d/m/Y') : null;
    }
}
