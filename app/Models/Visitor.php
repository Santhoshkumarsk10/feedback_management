<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'name',
        'company',
        'designation',
        'mobile',
        'email',
        'external_source',
        'external_id',
        'external_metadata',
        'is_active',
    ];

    protected $casts = [
        'external_metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'visitor_id');
    }

    public function feedbacks(): HasManyThrough
    {
        return $this->hasManyThrough(Feedback::class, Visit::class, 'visitor_id', 'visit_id');
    }

    /**
     * Generate an absolute unique human-friendly visitor ID.
     * e.g. VIS-2026-0001
     */
    public static function generateUniqueVisitorId(string $prefix = 'VIS'): string
    {
        $year = date('Y');
        $latest = static::where('visitor_id', 'like', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->visitor_id, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = sprintf('%s-%s-%04d', $prefix, $year, $nextNum++);
        } while (static::where('visitor_id', $candidate)->exists());

        return $candidate;
    }

    /**
     * Find or create/update a visitor from external 3rd-party API payload.
     */
    public static function upsertFromExternal(array $payload, string $source = 'third_party_api'): static
    {
        $externalId = (string) ($payload['external_id'] ?? $payload['visitor_id'] ?? $payload['id'] ?? $payload['pass_id'] ?? '');
        $mobile = isset($payload['mobile']) ? preg_replace('/[^0-9]/', '', (string) $payload['mobile']) : (isset($payload['phone']) ? preg_replace('/[^0-9]/', '', (string) $payload['phone']) : null);
        $email = !empty($payload['email']) ? strtolower(trim((string) $payload['email'])) : null;
        $name = trim((string) ($payload['name'] ?? $payload['visitor_name'] ?? 'Guest Visitor'));
        $company = trim((string) ($payload['company'] ?? $payload['visitor_company'] ?? ''));

        // 1. Try to find existing visitor by external_id or visitor_id
        $visitor = null;
        if (!empty($externalId)) {
            $visitor = static::where('external_id', $externalId)
                ->orWhere('visitor_id', $externalId)
                ->first();
        }

        // 2. Try match by mobile
        if (!$visitor && !empty($mobile)) {
            $visitor = static::where('mobile', $mobile)->first();
        }

        // 3. Try match by email
        if (!$visitor && !empty($email)) {
            $visitor = static::where('email', $email)->first();
        }

        // 4. Try match by exact name and company
        if (!$visitor && !empty($name) && !empty($company)) {
            $visitor = static::where('name', $name)->where('company', $company)->first();
        }

        // Build unique visitor_id
        $visitorId = $visitor?->visitor_id;
        if (empty($visitorId)) {
            $visitorId = !empty($externalId) ? $externalId : static::generateUniqueVisitorId('VIS');
            // Ensure unique if externalId collides with different visitor
            if (static::where('visitor_id', $visitorId)->where('id', '!=', $visitor?->id ?? 0)->exists()) {
                $visitorId = static::generateUniqueVisitorId('VIS');
            }
        }

        $attributes = [
            'visitor_id' => $visitorId,
            'name' => $name ?: 'Guest Visitor',
            'company' => $company ?: ($visitor?->company ?? null),
            'designation' => $payload['designation'] ?? $payload['visitor_designation'] ?? ($visitor?->designation ?? null),
            'mobile' => $mobile ?: ($visitor?->mobile ?? null),
            'email' => $email ?: ($visitor?->email ?? null),
            'external_source' => $source,
            'external_id' => !empty($externalId) ? $externalId : ($visitor?->external_id ?? null),
            'external_metadata' => $payload,
            'is_active' => true,
        ];

        if ($visitor) {
            $visitor->update($attributes);
            return $visitor->fresh();
        }

        return static::create($attributes);
    }
}
