<?php

namespace App\Helpers;

use App\Models\Admin\OnlinePayment;

class GTBankPayment
{
    public function makePayment(array $data)
    {
//        dd('GTBANK' ,$data);
        $min_amount = 0.1;
        $outstanding_fee = 10.00; //$data['amount_left'];
        $data['amount'] = 100.00;

        $min_amount = ($outstanding_fee >= $min_amount)? $min_amount:$outstanding_fee;

        if($data['amount'] < $min_amount){
            return back()->with('error', "Minimum amount you can pay is GHS ".$min_amount);
        }

        $amount = $data['amount'];
        $itemname = "TEST FEES"; //strtoupper(get_current_event()->name)." FEE";
        $clientref = "TACGH-OFF-25-0001-CVSH9-ORKJI"; //$data['transaction_no'];//$request['batch_no']."-".BatchRegistration::token(12);
        $clientid = env('GTPAY_CLIENTID',0);
        $clientsecret = env('GTPAY_SECRET',0);
        $hashkey = env('GTPAY_HASH',0);

        $raw_string = $amount.'&'.$itemname.'&'.$clientref.'&'.$clientsecret.'&'.$clientid;

        $hash_string = hash_hmac('sha256', $raw_string, $hashkey);
        $baseurl = env('GTPAY_BASEURL',0);

        $args = array(
            'amount' => $amount,
            'itemname' => $itemname,
            'clientref' => $clientref,
            'clientsecret' => $clientsecret,
            'clientid' => $clientid,
            'returnurl' => env('GTPAY_RETURNURL',0),
            'securehash' => $hash_string
        );

        $args_array = array();
        foreach ($args as $key => $value) {
            $args_array[] = "<input type='hidden' name='$key' value='$value'/>";
        }

        return ['inputs' => $args_array, 'url' => $baseurl];

//        return view('registrant.payment',compact('args_array','baseurl'));
    }

    public function paymentReceipt(array $data)
    {
        dd('Receipt', $data);
        $client_ref = explode("-",$data['clientref'])[1];
        $status = $data['statusCode'];

        $payment = OnlinePayment::firstOrCreate([
            'reg_id'=>$client_ref,
            'batch_no'=>$client_ref,
            'payment_mode'=>$data['paymentMode'],
            'transaction_no'=>$data['clientref'],
            'amount_paid'=>$data['amount'],
            'payment_status'=>$status,
            'approved'=>$status,
        ]);

        $message_success = (substr($client_ref,0,3) == "ACM")? "Payment of GHS ".$data['amount']." for Camper # ".$client_ref." was succesful!":
            "Payment of GHS ".$data['amount']." for Batch #".$client_ref." was successful!";

        $message_failure = (substr($client_ref,0,3) == "ACM")? "Payment of GHS ".$data['amount']." for Camper # ".$client_ref." was NOT succesful!":
            "Payment of GHS ".$data['amount']." for Batch #".$client_ref." was NOT successful!";
        if($status == 1){
//            $message = "Payment of GHS ".$request['amount']." for Batch #".$client_ref." was succesful!";
            alert()->success('Succesful',$message_success )->persistent('Close');
        }
        elseif($status == 0){
//        $message = "Payment of GHS ".$request['amount']." for Batch #".$client_ref." was not succesful!";
            alert()->info("Info",$message_failure)->persistent('Close');
        }

        if($this->checkClientType($client_ref) == "ACM"){
            return redirect('camper-info-update/2');
        }
        elseif($this->checkClientType($client_ref) == "BACM"){

            return redirect('chapter-info-update/'.$client_ref.'/1');
        }
    }
}
