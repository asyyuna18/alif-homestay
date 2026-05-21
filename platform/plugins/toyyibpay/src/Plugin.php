<?php

namespace Botble\ToyyibPay;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'payment_toyyibpay_name',
            'payment_toyyibpay_description',
            'payment_toyyibpay_logo',
            'payment_toyyibpay_user_secret_key',
            'payment_toyyibpay_category_code',
            'payment_toyyibpay_status',
            'payment_toyyibpay_fee',
            'payment_toyyibpay_fee_type',
            'payment_toyyibpay_available_countries',
        ]);
    }
}
