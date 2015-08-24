<?php

return [

    'driver' => env('MAIL_DRIVER', 'smtp'),
    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => env('MAIL_PORT', '587'),
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', null),
        'name' => env('MAIL_FROM_NAME', null),
    ],
    'encryption' => env('MAIL_TLS', 'tls'),
    'username' => env('MAIL_USERNAME', null),
    'password' => env('MAIL_PASSWORD', null),
    'sendmail' => env('MAIL_SENDMAIL', '/usr/sbin/sendmail -bs'),
    'pretend' => env('MAIL_PRETEND', false),

];
