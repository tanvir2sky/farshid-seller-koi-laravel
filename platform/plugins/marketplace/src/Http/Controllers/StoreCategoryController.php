<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Marketplace\Forms\StoreCategoryForm;
use Botble\Marketplace\Http\Requests\StoreCategoryRequest;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Models\StoreCategory;
use Botble\Marketplace\Tables\StoreCategoryTable;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Collection;

class StoreCategoryController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/marketplace::store-category.name'), route('marketplace.store-categories.index'));
    }

    public function index(StoreCategoryTable $table)
    {
        $this->pageTitle(trans('plugins/marketplace::store-category.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/marketplace::store-category.create'));

        $storeCategory = new StoreCategory();

        return view('plugins/marketplace::store-categories.form', [
            'storeCategory' => $storeCategory,
            'form' => StoreCategoryForm::create()
                ->setUrl(route('marketplace.store-categories.create.store'))
                ->renderForm(),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $storeCategory = StoreCategory::query()->create($request->validated());

        $request->merge(['is_slug_editable' => 1]);
        event(new CreatedContentEvent(STORE_CATEGORY_MODULE_SCREEN_NAME, $request, $storeCategory));

        if (! $storeCategory->slug) {
            SlugHelper::createSlug($storeCategory);
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.store-categories.index'))
            ->setNextUrl(route('marketplace.store-categories.edit', $storeCategory))
            ->withCreatedSuccessMessage();
    }

    public function edit(StoreCategory $storeCategory)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $storeCategory->name]));

        return view('plugins/marketplace::store-categories.form', [
            'storeCategory' => $storeCategory,
            'form' => StoreCategoryForm::createFromModel($storeCategory)
                ->setUrl(route('marketplace.store-categories.edit.update', $storeCategory))
                ->renderForm(),
        ]);
    }

    public function update(StoreCategory $storeCategory, StoreCategoryRequest $request)
    {
        $storeCategory->update($request->validated());

        $request->merge(['is_slug_editable' => 1]);
        event(new UpdatedContentEvent(STORE_CATEGORY_MODULE_SCREEN_NAME, $request, $storeCategory));

        if (! $storeCategory->slug) {
            SlugHelper::createSlug($storeCategory);
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.store-categories.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(StoreCategory $storeCategory)
    {
        return DeleteResourceAction::make($storeCategory);
    }

    public function getListForSelect()
    {
        $categories = StoreCategory::query()
            ->toBase()
            ->select([
                'id',
                'name',
                'parent_id',
            ])
            ->oldest('order')
            ->latest()
            ->get();

        $grouped = $categories->groupBy(fn ($row) => (int) ($row->parent_id ?: 0));

        return $this
            ->httpResponse()
            ->setData($this->buildTree($grouped));
    }

    protected function buildTree(
        Collection $categories,
        ?Collection $tree = null,
        int|string $parentId = 0,
        ?string $indent = null
    ): Collection {
        if ($tree === null) {
            $tree = collect();
        }

        $currentCategories = $categories->get($parentId);

        if ($currentCategories) {
            foreach ($currentCategories as $category) {
                $tree->push([
                    'id' => $category->id,
                    'name' => $indent . ' ' . $category->name,
                ]);

                if ($categories->has($category->id)) {
                    $this->buildTree($categories, $tree, $category->id, $indent . '&nbsp;&nbsp;');
                }
            }
        }

        return $tree;
    }
}
