<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscalationRule extends Model
{
    protected $fillable = ['priority_id', 'escalation_minutes', 'escalate_to', 'level', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function escalateTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalate_to');
    }
}
