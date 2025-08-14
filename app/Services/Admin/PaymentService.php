<?php

namespace App\Services\Admin;

use App\Models\Admin\OnlinePayment;
use App\Models\BatchLog;
use App\Models\Registrant;
use App\Models\RegistrantStage;

class PaymentService
{
    public function makePayment(array $data)
    {
        if(isset($data['batch'])){
            $reg = BatchLog::find($data['batch_id']);
        } else {
            $reg = RegistrantStage::find($data['stage_id']);
        }

        $data = [
            'email' => $reg['email'],
            'amount' => $data['total_fee'] * 100,
            'metadata' => [
                'name' => event_registrant_name($reg['id']),
                'phone' => $reg['phone_number']
            ],
            'callback_url' => isset($data['batch']) ? route('registrant_page_batch') : route('registrant_page'),
        ];

        return $data;
    }

    public function paymentReceipt(array $data, $paymentDetails, $response)
    {
        if(isset($data['batch'])){
            $batch_payment = session('batch_payment')['reg'];
            foreach ($data['batch'] as $data){
                $confirmed_registrant = Registrant::where('stage_id', $data['id'])->first();
                $amount_paid = collect($batch_payment)->where('registrant_id', $data['id'])->first();
//                dd($data['event_id'], $confirmed_registrant, $amount_paid['amount_paid']);
                OnlinePayment::create([
                    'reg_id' => $data['id'],
                    'payment_mode' => $paymentDetails['channel'],
                    'transaction_no' => $paymentDetails['id'],
                    'amount_to_pay' => $confirmed_registrant->total_fee,
                    'amount_paid' => $amount_paid['amount_paid'],
                    'date_paid' => date('Y-m-d', strtotime($paymentDetails['transaction_date'])),
                    'comment' => $response['message'],
                    'approved' => 1,
                    'approved_at' => date('Y-m-d', strtotime($paymentDetails['paid_at'])),
                    'batch_no' => $data['batch_no'],
                    'event_total_fee' => $confirmed_registrant->total_fee,
                    'payment_token' => $paymentDetails['id'],
                    'payment_status' => $response['status'],
                    'event_id' => $data['event_id'],
                ]);
            }
        } else {
            OnlinePayment::create([
                'reg_id' => $data['registrant']['id'],
                'payment_mode' => $paymentDetails['channel'],
                'transaction_no' => $paymentDetails['id'],
                'amount_to_pay' => $data['confirmed_registrant']->total_fee,
                'amount_paid' => $paymentDetails['amount'] / 100,
                'date_paid' => date('Y-m-d', strtotime($paymentDetails['transaction_date'])),
                'comment' => $response['message'],
                'approved' => 1,
                'approved_at' => date('Y-m-d', strtotime($paymentDetails['paid_at'])),
                'batch_no' => $data['registrant']['batch_no'],
                'event_total_fee' => $data['confirmed_registrant']->total_fee,
                'payment_token' => $paymentDetails['id'],
                'payment_status' => $response['status'],
                'event_id' => $data['registrant']['event_id'],
            ]);
        }
    }
}
