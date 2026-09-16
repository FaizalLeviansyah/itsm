<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetAudit extends Model
{
    protected $fillable = [
        'audit_number', 'title', 'description', 'auditor_id', 'company_id',
        'status', 'scheduled_date', 'completed_date', 'location', 'vessel_name',
        'total_assets', 'found_assets', 'missing_assets', 'damaged_assets', 'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
    ];

    public static function generateNumber(): string
    {
        $last = static::count() + 1;
        return sprintf('AUD-%s-%04d', now()->format('Ym'), $last);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssetAuditItem::class);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_assets === 0) return 0;
        return round(($this->items()->whereIn('scan_status', ['scanned', 'manual'])->count() / $this->total_assets) * 100);
    }
}
