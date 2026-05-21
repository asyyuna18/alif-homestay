<?php

namespace Botble\ToyyibPay\Services\Gateways;

use Botble\Payment\Enums\PaymentStatusEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ToyyibPayPaymentService
{
    protected const API_BASE_URL = 'https://toyyibpay.com/index.php/api';

    public function createBill(array $data): array
    {
        $secretKey = get_payment_setting('user_secret_key', TOYYIBPAY_PAYMENT_METHOD_NAME);
        $categoryCode = get_payment_setting('category_code', TOYYIBPAY_PAYMENT_METHOD_NAME);

        if (! $secretKey || ! $categoryCode) {
            return [
                'error' => true,
                'message' => trans('plugins/toyyibpay::toyyibpay.missing_settings'),
            ];
        }

        $orderId = (string) Arr::first((array) Arr::get($data, 'order_id', []));
        $billName = $this->sanitizeText('Booking ' . $orderId, 30);
        $billDescription = $this->sanitizeText((string) Arr::get($data, 'description', $billName), 100);
        $customerName = $this->sanitizeText((string) Arr::get($data, 'address.name', 'Guest'), 60);
        $customerEmail = (string) Arr::get($data, 'address.email');
        $customerPhone = preg_replace('/\D+/', '', (string) Arr::get($data, 'address.phone'));

        $payload = [
            'userSecretKey' => $secretKey,
            'categoryCode' => $categoryCode,
            'billName' => $billName,
            'billDescription' => $billDescription,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => (int) round(((float) Arr::get($data, 'amount', 0)) * 100),
            'billReturnUrl' => route('payments.toyyibpay.return'),
            'billCallbackUrl' => route('payments.toyyibpay.callback'),
            'billExternalReferenceNo' => $orderId,
            'billTo' => $customerName ?: 'Guest',
            'billEmail' => $customerEmail,
            'billPhone' => $customerPhone,
        ];

        do_action('payment_before_making_api_request', TOYYIBPAY_PAYMENT_METHOD_NAME, $payload);

        $response = Http::asForm()
            ->timeout(30)
            ->post(self::API_BASE_URL . '/createBill', $payload);

        $responseData = $response->json();

        do_action('payment_after_api_response', TOYYIBPAY_PAYMENT_METHOD_NAME, $payload, [
            'status' => $response->status(),
            'body' => $responseData ?: $response->body(),
        ]);

        if (! $response->successful() || ! is_array($responseData) || empty($responseData[0]['BillCode'])) {
            return [
                'error' => true,
                'message' => trans('plugins/toyyibpay::toyyibpay.create_bill_failed'),
            ];
        }

        $billCode = $responseData[0]['BillCode'];

        return [
            'error' => false,
            'bill_code' => $billCode,
            'checkout_url' => $this->billUrl($billCode),
            'response' => $responseData[0],
        ];
    }

    public function getBillTransactions(string $billCode, ?string $billPaymentStatus = null): array
    {
        $payload = [
            'billCode' => $billCode,
        ];

        if ($billPaymentStatus) {
            $payload['billpaymentStatus'] = $billPaymentStatus;
        }

        do_action('payment_before_making_api_request', TOYYIBPAY_PAYMENT_METHOD_NAME, $payload);

        $response = Http::asForm()
            ->timeout(30)
            ->post(self::API_BASE_URL . '/getBillTransactions', $payload);

        $responseData = $response->json();

        do_action('payment_after_api_response', TOYYIBPAY_PAYMENT_METHOD_NAME, $payload, [
            'status' => $response->status(),
            'body' => $responseData ?: $response->body(),
        ]);

        if (! $response->successful() || ! is_array($responseData)) {
            return [
                'error' => true,
                'message' => trans('plugins/toyyibpay::toyyibpay.fetch_transaction_failed'),
                'data' => [],
            ];
        }

        return [
            'error' => false,
            'message' => null,
            'data' => $responseData,
        ];
    }

    public function verifyCallbackHash(string $status, string $orderId, string $referenceNumber, ?string $receivedHash): bool
    {
        if (! $receivedHash) {
            return false;
        }

        $secretKey = get_payment_setting('user_secret_key', TOYYIBPAY_PAYMENT_METHOD_NAME);

        if (! $secretKey) {
            return false;
        }

        $expectedHash = md5($secretKey . $status . $orderId . $referenceNumber . 'ok');

        return hash_equals($expectedHash, $receivedHash);
    }

    public function mapPaymentStatus(string|int|null $status): string
    {
        return match ((string) $status) {
            '1' => PaymentStatusEnum::COMPLETED,
            '2', '4' => PaymentStatusEnum::PENDING,
            '3' => PaymentStatusEnum::FAILED,
            default => PaymentStatusEnum::FAILED,
        };
    }

    public function billUrl(string $billCode): string
    {
        return 'https://toyyibpay.com/' . $billCode;
    }

    protected function sanitizeText(string $text, int $limit): string
    {
        $clean = strip_tags($text);
        $clean = preg_replace('/\s+/', ' ', $clean ?? '');
        $clean = preg_replace('/[^A-Za-z0-9 _-]/', '', $clean ?? '');
        $clean = trim($clean ?? '');

        if (! $clean) {
            $clean = 'Payment';
        }

        return Str::limit($clean, $limit, '');
    }
}
