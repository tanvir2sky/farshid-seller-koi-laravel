<?php

namespace Botble\PopupAds\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\PopupAds\Models\PopupAdAnalytic;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends BaseController
{
    public function recordImpression(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'popup_ad_id' => ['required', 'integer', 'exists:popup_ads,id'],
        ]);

        PopupAdAnalytic::query()
            ->firstOrCreate(
                ['popup_ad_id' => $validated['popup_ad_id'], 'date' => Carbon::today()->toDateString()],
                ['impressions' => 0, 'clicks' => 0]
            );

        PopupAdAnalytic::query()
            ->where('popup_ad_id', $validated['popup_ad_id'])
            ->where('date', Carbon::today()->toDateString())
            ->increment('impressions');

        return response()->json(['success' => true]);
    }

    public function recordClick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'popup_ad_id' => ['required', 'integer', 'exists:popup_ads,id'],
        ]);

        PopupAdAnalytic::query()
            ->firstOrCreate(
                ['popup_ad_id' => $validated['popup_ad_id'], 'date' => Carbon::today()->toDateString()],
                ['impressions' => 0, 'clicks' => 0]
            );

        PopupAdAnalytic::query()
            ->where('popup_ad_id', $validated['popup_ad_id'])
            ->where('date', Carbon::today()->toDateString())
            ->increment('clicks');

        return response()->json(['success' => true]);
    }
}
