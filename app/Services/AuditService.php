<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log(string $action, string $description, $subject = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
        ]);
    }
}
