<?php

return [
    'default' => env('MAIL_MAILER', env('MAIL_DRIVER', 'log')),
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => 30,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],
        'mailgun' => ['transport' => 'mailgun'],
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],
        'log' => ['transport' => 'log'],
        'array' => ['transport' => 'array'],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'no-reply@todolog.app'),
        'name' => env('MAIL_FROM_NAME', 'Todolog'),
    ],
];
