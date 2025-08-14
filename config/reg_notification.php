<?php

return [
//    APIV1_MNOTIFY_API_KEY
    'sms_mnotify' => [
        'api_key'=>env('APIV2_MNOTIFY_API_KEY',''),
        'api_endpoint'=>env('MNOTIFY_ENDPOINT',''),
        'sender_id'=>env('MNOTIFY_SENDER_ID',''),
//        'sms_mgs'=>env('SMS_MSG',''),
    ],

    'whatsapp_waapi' => [
        'apiKey' => env('WHATSAPP_APIKEY',''),
        'api_endpoint' => env('WHATSAPP_ENDPOINT',''),
    ]

];
