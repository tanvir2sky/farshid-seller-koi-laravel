<?php

namespace Botble\PopupAds\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\PopupAds\Models\PopupAd;
use Botble\PopupAds\Models\PopupAdAnalytic;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PopupAdAnalyticsController extends BaseController
{
    public function index(PopupAd $popupAd, Request $request)
    {
        PageTitle::setTitle(trans('plugins/popup-ads::popup-ads.analytics.title', ['name' => $popupAd->name]));

        $days = (int) $request->input('days', 30);
        $days = in_array($days, [7, 14, 30]) ? $days : 30;

        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $rows = PopupAdAnalytic::query()
            ->where('popup_ad_id', $popupAd->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get(['date', 'impressions', 'clicks'])
            ->keyBy(fn ($r) => $r->date->toDateString());

        // Build a full date range including days with zero data
        $dateRange = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $key = $cursor->toDateString();
            $dateRange[] = [
                'date'        => $key,
                'impressions' => $rows->has($key) ? (int) $rows[$key]->impressions : 0,
                'clicks'      => $rows->has($key) ? (int) $rows[$key]->clicks : 0,
            ];
            $cursor->addDay();
        }

        $totalImpressions = array_sum(array_column($dateRange, 'impressions'));
        $totalClicks      = array_sum(array_column($dateRange, 'clicks'));
        $ctr              = $totalImpressions > 0
            ? round(($totalClicks / $totalImpressions) * 100, 2)
            : 0;

        return view('plugins/popup-ads::analytics', compact(
            'popupAd',
            'dateRange',
            'totalImpressions',
            'totalClicks',
            'ctr',
            'days'
        ));
    }
}
