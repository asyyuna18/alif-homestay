<?php

namespace Botble\ToyyibPay\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Hotel\Models\Booking;
use Botble\Hotel\Models\Customer;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentHelper;
use Botble\ToyyibPay\Services\Gateways\ToyyibPayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class ToyyibPayController extends BaseController
{
    public function callback(Request $request, ToyyibPayPaymentService $toyyibPayPaymentService): Response
    {
        $status = (string) $request->input('status');
        $orderId = (string) $request->input('order_id');
        $billCode = (string) $request->input('billcode');
        $referenceNumber = (string) $request->input('refno');
        $hash = $request->input('hash');

        if (! $status || ! $orderId || ! $billCode || ! $referenceNumber) {
            return response('Missing callback data', 400);
        }

        if (! $toyyibPayPaymentService->verifyCallbackHash($status, $orderId, $referenceNumber, $hash)) {
            return response('Invalid callback hash', 400);
        }

        $this->syncPaymentStatus($toyyibPayPaymentService, $billCode, $orderId, $status);

        return response('OK');
    }

    public function return(Request $request, ToyyibPayPaymentService $toyyibPayPaymentService, BaseHttpResponse $response)
    {
        $status = (string) $request->input('status_id');
        $orderId = (string) $request->input('order_id');
        $billCode = (string) $request->input('billcode');

        if (! $status || ! $orderId || ! $billCode) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->setMessage(trans('plugins/toyyibpay::toyyibpay.invalid_return_data'));
        }

        $paymentStatus = $this->syncPaymentStatus($toyyibPayPaymentService, $billCode, $orderId, $status);

        if ($paymentStatus === PaymentStatusEnum::COMPLETED) {
            return $response
                ->setNextUrl(PaymentHelper::getRedirectURL())
                ->setMessage(trans('plugins/payment::payment.checkout_success'));
        }

        if ($paymentStatus === PaymentStatusEnum::PENDING) {
            return $response
                ->setNextUrl(PaymentHelper::getRedirectURL())
                ->setMessage(trans('plugins/payment::payment.payment_pending'));
        }

        return $response
            ->setError()
            ->setNextUrl(PaymentHelper::getCancelURL())
            ->setMessage(trans('plugins/toyyibpay::toyyibpay.payment_failed'));
    }

    protected function syncPaymentStatus(
        ToyyibPayPaymentService $toyyibPayPaymentService,
        string $billCode,
        string $orderId,
        string $fallbackStatus
    ): string {
        $transactionResponse = $toyyibPayPaymentService->getBillTransactions($billCode);

        $transaction = Arr::first(Arr::get($transactionResponse, 'data', []), function (array $item) use ($orderId) {
            return (string) Arr::get($item, 'billExternalReferenceNo') === (string) $orderId;
        });

        $mappedStatus = $toyyibPayPaymentService->mapPaymentStatus(
            Arr::get($transaction, 'billpaymentStatus', $fallbackStatus)
        );

        $booking = Booking::query()->find($orderId);

        if (! $booking) {
            return $mappedStatus;
        }

        $existingPayment = Payment::query()
            ->where('charge_id', $billCode)
            ->where('order_id', $booking->getKey())
            ->first();

        if ($existingPayment && $existingPayment->status === $mappedStatus) {
            return $mappedStatus;
        }

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => (float) Arr::get($transaction, 'billpaymentAmount', $booking->amount),
            'currency' => 'MYR',
            'charge_id' => $billCode,
            'order_id' => [$booking->getKey()],
            'customer_id' => $booking->customer_id,
            'customer_type' => $booking->customer_id ? Customer::class : null,
            'payment_channel' => TOYYIBPAY_PAYMENT_METHOD_NAME,
            'status' => $mappedStatus,
        ]);

        return $mappedStatus;
    }
}
