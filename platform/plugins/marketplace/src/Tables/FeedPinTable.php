<?php

namespace Botble\Marketplace\Tables;

use Botble\Marketplace\Models\FeedPin;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class FeedPinTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(FeedPin::class)
            ->addActions([
                EditAction::make()->route('marketplace.feed-pins.edit'),
                DeleteAction::make()->route('marketplace.feed-pins.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('pin_type', function (FeedPin $item) {
                return $item->pin_type instanceof \Botble\Marketplace\Enums\FeedPinTypeEnum
                    ? $item->pin_type->label()
                    : e((string) $item->pin_type);
            })
            ->editColumn('is_enabled', function (FeedPin $item) {
                return $item->is_enabled
                    ? trans('core/base::base.yes')
                    : trans('core/base::base.no');
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
                'pin_type',
                'target_id',
                'priority',
                'starts_at',
                'ends_at',
                'is_enabled',
                'created_at',
            ])
            ->oldest('priority')
            ->latest('id');
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('pin_type')
                ->title(trans('plugins/marketplace::feed-pin.table.pin_type'))
                ->alignStart(),
            Column::make('target_id')
                ->title(trans('plugins/marketplace::feed-pin.table.target_id'))
                ->width(100),
            Column::make('priority')
                ->title(trans('plugins/marketplace::feed-pin.table.priority'))
                ->width(90),
            Column::make('starts_at')
                ->title(trans('plugins/marketplace::feed-pin.table.starts_at')),
            Column::make('ends_at')
                ->title(trans('plugins/marketplace::feed-pin.table.ends_at')),
            Column::make('is_enabled')
                ->title(trans('plugins/marketplace::feed-pin.table.is_enabled'))
                ->width(100),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(
            route('marketplace.feed-pins.create'),
            'marketplace.feed-pins.create'
        );
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('marketplace.feed-pins.destroy'),
        ];
    }
}
