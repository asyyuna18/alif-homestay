@if (setting('payment_toyyibpay_status') == 1)
    <x-plugins-payment::payment-method
        :name="TOYYIBPAY_PAYMENT_METHOD_NAME"
        paymentName="ToyyibPay"
        :supportedCurrencies="['MYR']"
    />
@endif
