<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogger
{
    public static function log(User $actor, string $action, string $entityType, string|int|null $entityId, array $details = []): void
    {
        AuditLog::create([
            'actor_id' => $actor->id,
            'actor_email' => $actor->email,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}
