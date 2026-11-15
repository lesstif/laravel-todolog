<?php

return [
    'driver' => 'bcrypt',
    'bcrypt' => [
        'rounds' => (int) env('BCRYPT_ROUNDS', 12),
        'verify' => true,
    ],
    'rehash_on_login' => true,
];
