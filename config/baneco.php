<?php

return [
    'base_url' => env('BANECO_QR_URL', 'https://apimktdesa.baneco.com.bo/ApiGateway'),
    'user' => env('BANECO_QR_USER', '26551010'),
    'password' => env('BANECO_QR_PASSWORD', '1234'),
    'aes_key' => env('BANECO_QR_AES_KEY', '40A318B299F245C2B697176723088629'),
    'account' => env('BANECO_QR_ACCOUNT', '1061602532'),
    'timeout_minutes' => (int) env('BANECO_QR_TIMEOUT_MINUTES', 4),
    'currency' => 'BOB',
];
