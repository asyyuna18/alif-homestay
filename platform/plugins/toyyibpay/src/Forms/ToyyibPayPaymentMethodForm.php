<?php

namespace Botble\ToyyibPay\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Concerns\Forms\HasAvailableCountriesField;
use Botble\Payment\Forms\PaymentMethodForm;

class ToyyibPayPaymentMethodForm extends PaymentMethodForm
{
    use HasAvailableCountriesField;

    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(TOYYIBPAY_PAYMENT_METHOD_NAME)
            ->paymentName('ToyyibPay')
            ->paymentDescription(trans('plugins/toyyibpay::toyyibpay.description'))
            ->paymentLogo(url('vendor/core/plugins/toyyibpay/images/toyyibpay.svg'))
            ->paymentFeeField(TOYYIBPAY_PAYMENT_METHOD_NAME)
            ->paymentUrl('https://toyyibpay.com')
            ->defaultDescriptionValue(trans('plugins/toyyibpay::toyyibpay.redirect_message'))
            ->paymentInstructions(view('plugins/toyyibpay::instructions')->render())
            ->add(
                sprintf('payment_%s_user_secret_key', TOYYIBPAY_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/toyyibpay::toyyibpay.user_secret_key'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('user_secret_key', TOYYIBPAY_PAYMENT_METHOD_NAME))
            )
            ->add(
                sprintf('payment_%s_category_code', TOYYIBPAY_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/toyyibpay::toyyibpay.category_code'))
                    ->value(get_payment_setting('category_code', TOYYIBPAY_PAYMENT_METHOD_NAME))
            )
            ->paymentMethodLogoField(TOYYIBPAY_PAYMENT_METHOD_NAME)
            ->addAvailableCountriesField(TOYYIBPAY_PAYMENT_METHOD_NAME);
    }
}
