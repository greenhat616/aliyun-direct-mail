<?php

return [
    'access_key_id' => env('DIRECT_MAIL_ACCESS_SECRET_ID'),
    'access_key_secret' => env('DIRECT_MAIL_ACCESS_SECRET_KEY'),
    'region' => 'cn-hangzhou',
    'account_name' => env('DIRECT_MAIL_ACCOUNT_NAME'),
    'reply_to' => env('DIRECT_MAIL_REPLY_TO'),
    'from_alias' => env('DIRECT_MAIL_ACCOUNT_ALIAS'),
];