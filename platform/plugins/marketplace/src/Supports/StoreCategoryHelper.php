<?php

namespace Botble\Marketplace\Supports;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Marketplace\Models\StoreCategory;
use Illuminate\Support\Collection;

class StoreCategoryHelper
{
    public static function getTreeCategories(bool $activeOnly = false): Collection
    {
        $query = StoreCategory::query()
            ->where(function ($query): void {
                $query
                    ->whereNull('parent_id')
                    ->orWhere('parent_id', 0);
            })
            ->orderBy('order')
            ->latest();

        if ($activeOnly) {
            $query->where('status', BaseStatusEnum::PUBLISHED);
        }

        $childRelation = $activeOnly ? 'activeChildren' : 'children';

        return $query->with(['slugable', $childRelation])->get();
    }

    public static function getTreeCategoriesOptions(Collection $categories, array $options = [], ?string $indent = ''): array
    {
        foreach ($categories as $category) {
            $options[$category->id] = $indent . $category->name;

            $nested = $category->relationLoaded('activeChildren') && $category->activeChildren->isNotEmpty()
                ? $category->activeChildren
                : ($category->relationLoaded('children') ? $category->children : collect());

            if ($nested->isNotEmpty()) {
                $options = static::getTreeCategoriesOptions(
                    $nested,
                    $options,
                    $indent . '&nbsp;&nbsp;'
                );
            }
        }

        return $options;
    }
}
