<?php

use App\Listeners\LogLogin;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;

return [
    Login::class => [
        LogLogin::class,
    ],
    Failed::class => [
        LogLogin::class,
    ],
];
