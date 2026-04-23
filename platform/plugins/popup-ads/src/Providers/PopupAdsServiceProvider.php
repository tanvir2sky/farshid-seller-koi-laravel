<?php

namespace Botble\PopupAds\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\PopupAds\Models\PopupAd;
use Botble\PopupAds\Repositories\Eloquent\PopupAdRepository;
use Botble\PopupAds\Repositories\Interfaces\PopupAdInterface;
use Illuminate\Support\ServiceProvider;

class PopupAdsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(PopupAdInterface::class, function () {
            return new PopupAdRepository(new PopupAd());
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/popup-ads')
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadHelpers()
            ->loadAndPublishViews();

        DashboardMenu::beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id'          => 'cms-plugins-popup-ads',
                    'priority'    => 9,
                    'icon'        => 'ti ti-layout-board-split',
                    'name'        => 'plugins/popup-ads::popup-ads.name',
                    'permissions' => ['popup-ads.index'],
                ])
                ->registerItem([
                    'id'          => 'cms-plugins-popup-ads-list',
                    'parent_id'   => 'cms-plugins-popup-ads',
                    'priority'    => 1,
                    'name'        => 'plugins/popup-ads::popup-ads.name',
                    'url'         => fn () => route('popup-ads.index'),
                    'permissions' => ['popup-ads.index'],
                ]);
        });

        // Inject popup markup before the closing </body> tag via the theme footer filter
        if (defined('THEME_FRONT_FOOTER')) {
            add_filter(THEME_FRONT_FOOTER, function (?string $html): string {
                if (! config('plugins.popup-ads.general.inject_in_theme', true)) {
                    return $html ?? '';
                }

                try {
                    $activeAds = PopupAd::query()->active()->orderBy('order')->get();

                    if ($activeAds->isEmpty()) {
                        return $html ?? '';
                    }

                    $popup = view('plugins/popup-ads::partials.popup', ['ads' => $activeAds])->render();

                    return ($html ?? '') . $popup;
                } catch (\Throwable $e) {
                    return $html ?? '';
                }
            }, 130);
        }
    }
}
