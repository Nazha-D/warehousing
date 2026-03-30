<?php
/**
* ============================================
* Enum: SalesOrderStatusEnum
* ============================================
* Matches the agreed commercial flow EXACTLY
*/

namespace App\Enums;

enum SalesOrderStatusEnum: string
{
case PROCESSING = 'processing';
case PARTIALLY_DELIVERED = 'partially_delivered';
case COMPLETED = 'completed';
case CANCELLED = 'cancelled';

public static function initial(): self
{
return self::PROCESSING;
}

public function canTransitionTo(self $to): bool
{
return match ($this) {
self::PROCESSING => in_array($to, [
self::PARTIALLY_DELIVERED,
self::CANCELLED,
], true),

self::PARTIALLY_DELIVERED => in_array($to, [
self::COMPLETED,
self::PROCESSING, // when deliveries are voided back to 0
], true),

self::COMPLETED => false,

self::CANCELLED => false,
};
}
}
