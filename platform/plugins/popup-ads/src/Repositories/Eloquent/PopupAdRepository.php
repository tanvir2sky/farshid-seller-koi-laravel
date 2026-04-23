<?php

namespace Botble\PopupAds\Repositories\Eloquent;

use Botble\PopupAds\Repositories\Interfaces\PopupAdInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class PopupAdRepository extends RepositoriesAbstract implements PopupAdInterface
{
    public function getActive(): Collection
    {
        $data = $this->model->active()->orderBy('order');

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
