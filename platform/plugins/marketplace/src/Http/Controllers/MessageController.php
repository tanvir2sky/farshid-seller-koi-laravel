<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Message;

class MessageController extends BaseController
{
    public function index()
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $threads = Message::query()
            ->with(['store', 'customer'])
            ->whereNotNull('customer_id')
            ->whereIn('id', Message::query()
                ->selectRaw('MAX(id)')
                ->whereNotNull('customer_id')
                ->groupBy('store_id', 'customer_id'))
            ->latest('created_at')
            ->paginate(20);

        $guestMessages = Message::query()
            ->with('store')
            ->whereNull('customer_id')
            ->latest('created_at')
            ->paginate(10, ['*'], 'guest_page');

        $this->pageTitle(__('Messages'));

        return view('plugins/marketplace::messages.index', compact('threads', 'guestMessages'));
    }

    public function show(int|string $id)
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $seedMessage = Message::query()
            ->with(['store', 'customer'])
            ->findOrFail($id);

        $messages = $seedMessage->customer_id
            ? Message::query()
                ->where('store_id', $seedMessage->store_id)
                ->where('customer_id', $seedMessage->customer_id)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
            : collect([$seedMessage]);

        $this->pageTitle(__('Viewing message #:id', ['id' => $seedMessage->getKey()]));

        return view('plugins/marketplace::messages.show', compact('seedMessage', 'messages'));
    }
}
