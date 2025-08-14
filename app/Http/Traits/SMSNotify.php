<?php
/**
 * Created by PhpStorm.
 * User: mantey
 * Date: 09/11/2021
 * Time: 11:19 PM
 */

namespace App\Http\Traits;
use App\Services\NotificationService;
use GuzzleHttp\Client;

trait SMSNotify
{
    public function sendMessage($to, $message, $type='sms'): void
    {
//        dd($to, $message, $type);
        if($type == 'sms'){
            $this->sendSms($to,$message);
        }else if($type == 'whatsapp'){
//            $this->sendWhatsAppNotification($to,$message);
            $this->sendWhatsApp($to, $message);
        }
        else{
//              dd('message won\'t be delivered sendsms_value'.$this->sendsms);
            return;
        }

//        dd($to, $message, $type);
    }

    public function sendSms($to, $message)
    {
//        dd('SMS');
        try {
            $apiKey = config('reg_notification.sms_mnotify.api_key');//env('SMS_API_KEY');
            $endPoint = config('reg_notification.sms_mnotify.api_endpoint');
            $sender_id = config('reg_notification.sms_mnotify.sender_id');

            $contact = ["$to"];

            $url = $endPoint . '?key=' . $apiKey;
            $data = [
                'recipient' => $contact,
                'sender' => $sender_id,
                'message' => $message,
                'is_schedule' => 'false',
                'schedule_date' => ''
            ];

            $ch = curl_init();
            $headers = array();
            $headers[] = "Content-Type: application/json";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            $result = json_decode($result, TRUE);
            curl_close($ch);

            if ($result["code"] == "2000") {
                return $result;
            } else {
                return $result;
            }

        } catch (\Exception $e) {
            return json_encode(['code' => -99, 'message' => "Sorry some error occurred " . $e->getMessage()]);
        }
    }

    public function sendWhatsApp($to, $message)
    {
        $to = substr($to, 1);
        $apiKey = config('reg_notification.whatsapp_waapi.apiKey');
        $endpoint = config('reg_notification.whatsapp_waapi.api_endpoint');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'chatId' => $to.'@c.us',
                'message' => $message
            ]),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "authorization: Bearer $apiKey",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}
