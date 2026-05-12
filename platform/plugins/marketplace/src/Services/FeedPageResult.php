<?php

namespace Botble\Marketplace\Services;

use Botble\Ecommerce\Models\Customer;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FeedPageResult
{
    public function __construct(
        public Collection $items,
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
        public int $total,
    ) {
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function toPaginator(): LengthAwarePaginator
    {
        // LengthAwarePaginator recomputes lastPage from total; use a synthetic total so it matches our pin-aware lastPage.
        $syntheticTotal = max($this->items->count(), $this->lastPage * $this->perPage);

        return new LengthAwarePaginator(
            $this->items,
            $syntheticTotal,
            $this->perPage,
            $this->currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }
}
