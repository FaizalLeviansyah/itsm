<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Problem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'problem_number', 'title', 'description', 'root_cause', 'workaround',
        'solution', 'category_id', 'priority_id', 'owner_id', 'company_id',
        'status', 'impact', 'identified_at', 'resolved_at', 'affected_incidents',
    ];

    protected $casts = [
        'identified_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $last = static::withTrashed()->count() + 1;
        return sprintf('PRB-%05d', $last);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class)->withTimestamps();
    }
}
