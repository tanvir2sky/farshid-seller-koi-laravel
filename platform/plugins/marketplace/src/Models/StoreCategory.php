<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Contracts\HasTreeCategory as HasTreeCategoryContract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Traits\HasTreeCategory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreCategory extends BaseModel implements HasTreeCategoryContract
{
    use HasTreeCategory;

    protected $table = 'mp_store_categories';

    protected $fillable = [
        'name',
        'parent_id',
        'description',
        'order',
        'status',
        'image',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (StoreCategory $category): void {
            if (! $category->parent_id) {
                $category->parent_id = null;
            }
        });

        static::deleted(function (StoreCategory $category): void {
            $category->stores()->detach();

            $category->children()->each(fn (StoreCategory $child) => $child->delete());
        });
    }

    public function parent(): BelongsTo
    {
        return $this
            ->belongsTo(StoreCategory::class, 'parent_id')
            ->whereNot('parent_id', $this->getKey())
            ->withDefault();
    }

    public function children(): HasMany
    {
        return $this
            ->hasMany(StoreCategory::class, 'parent_id')
            ->whereNot('id', $this->getKey());
    }

    public function activeChildren(): HasMany
    {
        return $this
            ->children()
            ->wherePublished()
            ->orderBy('order')
            ->with(['slugable', 'activeChildren']);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(
            Store::class,
            'mp_store_category_store',
            'store_category_id',
            'store_id'
        );
    }

    /**
     * @return list<int>
     */
    public static function getSelfAndDescendantIds(StoreCategory $category): array
    {
        $ids = [$category->getKey()];
        $category->loadMissing('activeChildren');

        return static::appendActiveChildIds($category->activeChildren, $ids);
    }

    /**
     * @param  EloquentCollection<int, StoreCategory>  $children
     * @param  list<int>  $ids
     * @return list<int>
     */
    protected static function appendActiveChildIds(EloquentCollection $children, array $ids): array
    {
        foreach ($children as $child) {
            $ids[] = $child->getKey();
            $child->loadMissing('activeChildren');
            if ($child->activeChildren->isNotEmpty()) {
                $ids = static::appendActiveChildIds($child->activeChildren, $ids);
            }
        }

        return $ids;
    }
}
