<?php

namespace App\Enums;

enum QuotationStatusEnum: string
{
    case Draft= 'DRAFT';
    case Sent = 'SENT';
    case Cancelled = 'CANCELLED';
    case Lost = 'LOST';
    case Win = 'WIN';
}
