<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_tag', 'name', 'asset_category_id', 'description', 'manufacturer',
        'model', 'serial_number', 'status', 'assigned_to', 'location',
        'vessel_name', 'vessel_id', 'company_id', 'purchase_date', 'purchase_cost',
        'warranty_expiry', 'ip_address', 'mac_address', 'specifications', 'notes',
        // SOC Integration fields
        'soc_endpoint_id', 'hostname', 'antivirus_status', 'usb_status',
        'windows_update_status', 'pc_brand', 'pc_specs', 'installed_apps',
        'paired_hardware', 'soc_status', 'soc_last_seen', 'soc_synced_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
        'specifications' => 'array',
        'installed_apps' => 'array',
        'paired_hardware' => 'array',
        'soc_last_seen' => 'datetime',
        'soc_synced_at' => 'datetime',
    ];

    public static function generateAssetTag(): string
    {
        $last = static::withTrashed()->count() + 1;
        return sprintf('AST-%05d', $last);
    }

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class)->orderByDesc('created_at');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class)->withTimestamps();
    }

    public function getIsWarrantyActiveAttribute(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isFuture();
    }
}
