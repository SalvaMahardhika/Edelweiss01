<?php

namespace App\Enums;

enum PaymentType: string
{
    case DownPayment = 'down_payment';
    case Settlement = 'settlement';
    case Full = 'full';
}
