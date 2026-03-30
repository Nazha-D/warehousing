<?php

namespace App\Constants;

class WarehouseConstants
{
    const NUMBER_PREFIX = 'WH';           // البادئة لكود المستودع
    const NUMBER_MIN_LENGTH = 4;          // طول الرقم بعد البادئة
    const NUMBER_PAD_STR = '0';           // حرف التعبئة
    const CODE_SEPARATOR = '-';         // الفاصل بين البادئة والرقم
}
