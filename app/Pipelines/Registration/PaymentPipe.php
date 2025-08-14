<?php

namespace App\Pipelines\Registration;

use App\Services\Admin\PaymentService;

class PaymentPipe
{
    private PaymentService $paymentService;
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handle(array $data, \Closure $next)
    {
        $data = $this->paymentService->makePayment($data);

        return $next($data);
    }
}
