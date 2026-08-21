<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;

class LogLogin
{
    public function handle(Login $event): void
    {
        LoginLog::create([
            'user_id' => $event->user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => true,
            'type' => 'login',
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        LoginLog::create([
            'user_id' => $event->user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => false,
            'type' => 'login',
        ]);
    }
}
