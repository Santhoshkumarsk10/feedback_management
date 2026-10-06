<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'description',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to quickly record an audit trail event.
     */
    public static function record(
        string $action,
        string $module,
        string $description,
        ?array $details = null,
        ?User $user = null
    ): self {
        $actor = $user ?? auth()->user();

        return self::create([
            'user_id' => $actor?->id,
            'user_name' => $actor?->name ?? 'System Process',
            'user_role' => $actor?->role ?? 'system',
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'details' => $details,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent(),
        ]);
    }
}
