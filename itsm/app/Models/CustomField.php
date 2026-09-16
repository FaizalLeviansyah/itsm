<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomField extends Model
{
    protected $fillable = ['name', 'field_key', 'field_type', 'options', 'asset_category_id', 'is_required', 'sort_order', 'is_active'];
    protected $casts = ['options' => 'array', 'is_required' => 'boolean', 'is_active' => 'boolean'];

    public function assetCategory(): BelongsTo { return $this->belongsTo(AssetCategory::class); }
}
