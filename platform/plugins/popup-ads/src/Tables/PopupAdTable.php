<?php

namespace Botble\PopupAds\Tables;

use Botble\PopupAds\Models\PopupAd;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\DateBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Support\Facades\DB;

class PopupAdTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(PopupAd::class)
            ->addColumns([
                IdColumn::make(),
                ImageColumn::make(),
                NameColumn::make()->route('popup-ads.edit'),
                FormattedColumn::make('dismiss_duration')
                    ->title(trans('plugins/popup-ads::popup-ads.fields.dismiss_duration'))
                    ->alignStart()
                    ->getValueUsing(function (FormattedColumn $column) {
                        return PopupAd::getDismissDurationOptions()[$column->getItem()->dismiss_duration] ?? $column->getItem()->dismiss_duration;
                    }),
                Column::make('delay_seconds')
                    ->title(trans('plugins/popup-ads::popup-ads.fields.delay_seconds'))
                    ->alignStart(),
                Column::make('impressions_today')
                    ->title(trans('plugins/popup-ads::popup-ads.analytics.impressions_today'))
                    ->alignStart()
                    ->orderable(false)
                    ->searchable(false),
                Column::make('clicks_today')
                    ->title(trans('plugins/popup-ads::popup-ads.analytics.clicks_today'))
                    ->alignStart()
                    ->orderable(false)
                    ->searchable(false),
                DateColumn::make('started_at')->title(trans('plugins/popup-ads::popup-ads.fields.started_at')),
                DateColumn::make('ended_at')->title(trans('plugins/popup-ads::popup-ads.fields.ended_at')),
                StatusColumn::make(),
            ])
            ->addHeaderAction(CreateHeaderAction::make()->route('popup-ads.create'))
            ->addActions([
                EditAction::make()->route('popup-ads.edit'),
                Action::make('analytics')
                    ->label(trans('plugins/popup-ads::popup-ads.analytics.title', ['name' => '']))
                    ->icon('ti ti-chart-bar')
                    ->color('success')
                    ->route('popup-ads.analytics')
                    ->permission('popup-ads.analytics'),
                DeleteAction::make()->route('popup-ads.destroy'),
            ])
            ->addBulkAction(DeleteBulkAction::make()->permission('popup-ads.destroy'))
            ->addBulkChanges([
                NameBulkChange::make(),
                StatusBulkChange::make(),
                DateBulkChange::make()->name('started_at')->title(trans('plugins/popup-ads::popup-ads.fields.started_at')),
                DateBulkChange::make()->name('ended_at')->title(trans('plugins/popup-ads::popup-ads.fields.ended_at')),
            ])
            ->queryUsing(function ($query): void {
                $today = now()->toDateString();

                $query->select([
                    'popup_ads.id',
                    'popup_ads.image',
                    'popup_ads.name',
                    'popup_ads.dismiss_duration',
                    'popup_ads.delay_seconds',
                    'popup_ads.started_at',
                    'popup_ads.ended_at',
                    'popup_ads.status',
                    DB::raw("COALESCE((SELECT impressions FROM popup_ad_analytics WHERE popup_ad_id = popup_ads.id AND date = '{$today}' LIMIT 1), 0) AS impressions_today"),
                    DB::raw("COALESCE((SELECT clicks FROM popup_ad_analytics WHERE popup_ad_id = popup_ads.id AND date = '{$today}' LIMIT 1), 0) AS clicks_today"),
                ]);
            });
    }
}
