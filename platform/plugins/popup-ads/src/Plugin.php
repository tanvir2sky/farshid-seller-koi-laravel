<?php

namespace Botble\PopupAds;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('popup_ad_analytics');
        Schema::dropIfExists('popup_ads');
    }
}
