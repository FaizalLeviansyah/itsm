<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoAssignRule extends Model
{
    protected $fillable = [
        'category_id', 'sub_category_id', 'company_id', 'vessel_name',
        'source_type', 'assign_to', 'is_active', 'priority_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subCategory(): BelongsTo { return $this->belongsTo(SubCategory::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assign_to'); }

    /**
     * Find the best assignee based on ticket context.
     */
    public static function findAssignee(int $categoryId, ?int $subCategoryId = null, ?int $companyId = null, ?string $vesselName = null, ?string $sourceType = null): ?int
    {
        $query = static::where('is_active', true)
            ->where('category_id', $categoryId);

        // Try most specific first: category + sub_category + company + vessel
        $rules = $query->orderByRaw("
            (sub_category_id IS NOT NULL) DESC,
            (company_id IS NOT NULL) DESC,
            (vessel_name IS NOT NULL) DESC,
            (source_type != 'all') DESC,
            priority_order ASC
        ")->get();

        foreach ($rules as $rule) {
            // Check sub_category match
            if ($rule->sub_category_id && $rule->sub_category_id != $subCategoryId) continue;

            // Check company match
            if ($rule->company_id && $rule->company_id != $companyId) continue;

            // Check vessel match
            if ($rule->vessel_name && strtolower($rule->vessel_name) != strtolower($vesselName ?? '')) continue;

            // Check source_type match
            if ($rule->source_type !== 'all' && $rule->source_type !== $sourceType) continue;

            return $rule->assign_to;
        }

        return null;
    }
}
