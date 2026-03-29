<?php

namespace Botble\Marketplace\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Marketplace\Models\StoreCategory;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class StoreCategoryTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(StoreCategory::class)
            ->addActions([
                EditAction::make()->route('marketplace.store-categories.edit'),
                DeleteAction::make()->route('marketplace.store-categories.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('parent_id', function (StoreCategory $item) {
                return $item->parent?->name ? e($item->parent->name) : '&mdash;';
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        return $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'parent_id',
                'order',
                'status',
                'created_at',
            ])
            ->with(['parent']);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('marketplace.store-categories.edit'),
            Column::make('parent_id')
                ->title(trans('core/base::forms.parent'))
                ->alignStart()
                ->orderable(false)
                ->searchable(false),
            Column::make('order')
                ->title(trans('core/base::tables.order'))
                ->width(80),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(
            route('marketplace.store-categories.create'),
            'marketplace.store-category.create'
        );
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('marketplace.store-category.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:250',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'order' => [
                'title' => trans('core/base::tables.order'),
                'type' => 'number',
                'validate' => 'nullable|integer|min:0|max:10000',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
