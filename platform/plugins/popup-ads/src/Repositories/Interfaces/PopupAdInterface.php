<?php

namespace Botble\PopupAds\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface PopupAdInterface extends RepositoryInterface
{
    public function getActive(): Collection;
}
