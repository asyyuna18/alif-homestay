<?php

namespace Botble\ToyyibPay\Providers;

use Botble\Base\Facades\Html;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\ToyyibPay\Forms\ToyyibPayPaymentMethodForm;
use Botble\ToyyibPay\Services\Gateways\ToyyibPayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerToyyibPayMethod'], 30, 2);
        add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithToyyibPay'], 30, 2);
        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 230);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['TOYYIBPAY'] = TOYYIBPAY_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 30, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == TOYYIBPAY_PAYMENT_METHOD_NAME) {
                $value = 'ToyyibPay';
            }

            return $value;
        }, 30, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == TOYYIBPAY_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )->toHtml();
            }

            return $value;
        }, 30, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == TOYYIBPAY_PAYMENT_METHOD_NAME) {
                $data = ToyyibPayPaymentService::class;
            }

            return $data;
        }, 30, 2);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . ToyyibPayPaymentMethodForm::create()->renderForm();
    }

    public function registerToyyibPayMethod(?string $html, array $data): string
    {
        PaymentMethods::method(TOYYIBPAY_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/toyyibpay::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithToyyibPay(array $data, Request $request): array
    {
        if ($data['type'] !== TOYYIBPAY_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        $result = $this->app->make(ToyyibPayPaymentService::class)->createBill($paymentData);

        if ($result['error']) {
            $data['error'] = true;
            $data['message'] = $result['message'];

            return $data;
        }

        $data['checkoutUrl'] = $result['checkout_url'];
        $data['charge_id'] = $result['bill_code'];
        $data['message'] = trans('plugins/toyyibpay::toyyibpay.redirecting');

        return $data;
    }
}
