<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\Fronts\MessageReplyRequest;
use Botble\Marketplace\Models\Message;
use Illuminate\Support\Collection;

class MessageController extends BaseController
{
    public function index()
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $store = auth('customer')->user()->store;
        $tab = request()->input('tab') === 'archived' ? 'archived' : 'active';

        $threads = Message::query()
            ->with(['customer', 'store'])
            ->where('store_id', $store->getKey())
            ->whereIn('id', Message::query()
                ->selectRaw('MAX(id)')
                ->where('store_id', $store->getKey())
                ->whereNotNull('customer_id')
                ->groupBy('customer_id'))
            ->when(
                $tab === 'archived',
                fn ($query) => $query->whereNotNull('vendor_archived_at'),
                fn ($query) => $query->whereNull('vendor_archived_at')
            )
            ->latest('created_at')
            ->paginate(10, ['*'], 'conversations_page');

        $unreadCounts = Message::query()
            ->selectRaw('customer_id, COUNT(*) as unread_count')
            ->where('store_id', $store->getKey())
            ->whereNotNull('customer_id')
            ->where('sender_type', Message::SENDER_CUSTOMER)
            ->whereNull('read_at')
            ->groupBy('customer_id')
            ->pluck('unread_count', 'customer_id');

        $guestMessages = Message::query()
            ->where('store_id', $store->getKey())
            ->whereNull('customer_id')
            ->latest('created_at')
            ->paginate(10, ['*'], 'guest_page');

        $this->pageTitle(__('Messages'));

        return MarketplaceHelper::view(
            'vendor-dashboard.messages.index',
            compact('threads', 'unreadCounts', 'guestMessages', 'tab')
        );
    }

    public function show(string $id)
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $seedMessage = Message::query()
            ->where('store_id', auth('customer')->user()->store->id)
            ->with(['store', 'customer'])
            ->findOrFail($id);

        $messages = $seedMessage->customer_id
            ? $this->getConversationMessages($seedMessage->store_id, $seedMessage->customer_id)
            : collect([$seedMessage]);

        if ($seedMessage->customer_id) {
            $this->markAsReadForVendor($seedMessage->store_id, $seedMessage->customer_id);
        }

        $this->pageTitle(__('Viewing message #:id', ['id' => $seedMessage->getKey()]));

        $canReply = (bool) $seedMessage->customer_id;
        $isArchived = $seedMessage->customer_id
            ? $this->isArchivedForVendor($seedMessage->store_id, $seedMessage->customer_id)
            : false;

        return MarketplaceHelper::view(
            'vendor-dashboard.messages.show',
            compact('seedMessage', 'messages', 'canReply', 'isArchived')
        );
    }

    public function reply(string $id, MessageReplyRequest $request): BaseHttpResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $seedMessage = Message::query()
            ->where('store_id', auth('customer')->user()->store->id)
            ->with('store')
            ->findOrFail($id);

        if (! $seedMessage->customer_id) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(__('This message cannot receive replies.'));
        }

        $vendor = auth('customer')->user();

        Message::query()->create([
            'store_id' => $seedMessage->store_id,
            'customer_id' => $seedMessage->customer_id,
            'sender_type' => Message::SENDER_VENDOR,
            'sender_id' => $vendor->getKey(),
            'name' => $seedMessage->store->name,
            'email' => $seedMessage->store->email,
            'content' => $request->input('content'),
        ]);

        $this->restoreThreadForBothSides($seedMessage->store_id, $seedMessage->customer_id);

        return $this
            ->httpResponse()
            ->setData($this->threadData($seedMessage))
            ->setMessage(__('Reply sent successfully!'));
    }

    public function refresh(string $id): BaseHttpResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $seedMessage = Message::query()
            ->where('store_id', auth('customer')->user()->store->id)
            ->findOrFail($id);

        if ($seedMessage->customer_id) {
            $this->markAsReadForVendor($seedMessage->store_id, $seedMessage->customer_id);
        }

        return $this
            ->httpResponse()
            ->setData($this->threadData($seedMessage));
    }

    public function archive(string $id): BaseHttpResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $message = Message::query()
            ->where('store_id', auth('customer')->user()->store->id)
            ->findOrFail($id);

        abort_if(! $message->customer_id, 404);

        $this->archiveThreadForVendor($message->store_id, $message->customer_id);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.messages.index'))
            ->setData(['next_url' => route('marketplace.vendor.messages.index')])
            ->setMessage(__('Conversation archived.'));
    }

    public function unarchive(string $id): BaseHttpResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $message = Message::query()
            ->where('store_id', auth('customer')->user()->store->id)
            ->findOrFail($id);

        abort_if(! $message->customer_id, 404);

        $this->unarchiveThreadForVendor($message->store_id, $message->customer_id);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.messages.show', $message->getKey()))
            ->setData(['next_url' => route('marketplace.vendor.messages.show', $message->getKey())])
            ->setMessage(__('Conversation reopened.'));
    }

    protected function getConversationMessages(int $storeId, int $customerId): Collection
    {
        return Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    protected function markAsReadForVendor(int $storeId, int $customerId): void
    {
        Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->where('sender_type', Message::SENDER_CUSTOMER)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    protected function threadData(Message $seedMessage): array
    {
        $messages = $seedMessage->customer_id
            ? $this->getConversationMessages($seedMessage->store_id, $seedMessage->customer_id)
            : collect([$seedMessage]);

        return [
            'html' => view('plugins/marketplace::messages.partials.thread-items', [
                'messages' => $messages,
                'outgoingSenderType' => Message::SENDER_VENDOR,
            ])->render(),
            'last_message_id' => $messages->last()?->getKey(),
        ];
    }

    protected function isArchivedForVendor(int $storeId, int $customerId): bool
    {
        return Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->whereNotNull('vendor_archived_at')
            ->exists();
    }

    protected function archiveThreadForVendor(int $storeId, int $customerId): void
    {
        Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->update(['vendor_archived_at' => now()]);
    }

    protected function unarchiveThreadForVendor(int $storeId, int $customerId): void
    {
        Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->update(['vendor_archived_at' => null]);
    }

    protected function restoreThreadForBothSides(int $storeId, int $customerId): void
    {
        Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->update([
                'customer_archived_at' => null,
                'vendor_archived_at' => null,
            ]);
    }
}
