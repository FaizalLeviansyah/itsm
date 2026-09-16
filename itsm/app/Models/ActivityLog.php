<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'model_type', 'model_id', 'description', 'changes', 'ip_address'];
    protected $casts = ['changes' => 'array'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function log(string $action, string $description, $model = null, ?array $changes = null): void
    {
        static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id ?? $model?->getKey(),
            'description' => $description,
            'changes' => $changes,
            'ip_address' => Request::ip(),
        ]);
    }
}
