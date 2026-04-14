<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\Fronts\MessageReplyRequest;
use Botble\Marketplace\Models\Message;
use Botble\Marketplace\Models\Store;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Collection;

class CustomerMessageController extends BaseController
{
    public function index()
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $customer = auth('customer')->user();
        $tab = request()->input('tab') === 'archived' ? 'archived' : 'active';

        $threads = Message::query()
            ->with('store')
            ->whereIn('id', Message::query()
                ->selectRaw('MAX(id)')
                ->where('customer_id', $customer->getKey())
                ->groupBy('store_id'))
            ->when(
                $tab === 'archived',
                fn ($query) => $query->whereNotNull('customer_archived_at'),
                fn ($query) => $query->whereNull('customer_archived_at')
            )
            ->latest('created_at')
            ->paginate(10);

        $unreadCounts = Message::query()
            ->selectRaw('store_id, COUNT(*) as unread_count')
            ->where('customer_id', $customer->getKey())
            ->where('sender_type', Message::SENDER_VENDOR)
            ->whereNull('read_at')
            ->groupBy('store_id')
            ->pluck('unread_count', 'store_id');

        SeoHelper::setTitle(__('Messages'));

        Theme::breadcrumb()
            ->add(__('Messages'), route('customer.messages.index'));

        return Theme::scope(
            'marketplace.customers.messages.index',
            compact('threads', 'unreadCounts', 'tab'),
            'plugins/marketplace::themes.customers.messages.index'
        )->render();
    }

    public function show(int|string $storeId)
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $customer = auth('customer')->user();
        $store = $this->getAvailableStore($storeId, $customer);

        $this->markAsReadForCustomer($store->getKey(), $customer->getKey());

        $messages = $this->getConversationMessages($store->getKey(), $customer->getKey());
        $isArchived = $this->isArchivedForCustomer($store->getKey(), $customer->getKey());

        SeoHelper::setTitle(__('Messages'));

        Theme::breadcrumb()
            ->add(__('Messages'), route('customer.messages.index'))
            ->add($store->name, route('customer.messages.show', $store->getKey()));

        return Theme::scope(
            'marketplace.customers.messages.show',
            compact('store', 'messages', 'isArchived'),
            'plugins/marketplace::themes.customers.messages.show'
        )->render();
    }

    public function store(int|string $storeId, MessageReplyRequest $request): BaseHttpResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $customer = auth('customer')->user();
        $store = $this->getAvailableStore($storeId, $customer);

        Message::query()->create([
            'store_id' => $store->getKey(),
            'customer_id' => $customer->getKey(),
            'sender_type' => Message::SENDER_CUSTOMER,
            'sender_id' => $customer->getKey(),
            'name' => $customer->name,
            'email' => $customer->email,
            'content' => $request->input('content'),
        ]);

        $this->restoreThreadForBothSides($store->getKey(), $customer->getKey());
        $this->markAsReadForCustomer($store->getKey(), $customer->getKey());

        return $this
            ->httpResponse()
            ->setData($this->threadData($store, $customer))
            ->setMessage(__('Send message successfully!'));
    }

    public function refresh(int|string $storeId): BaseHttpResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $customer = auth('customer')->user();
        $store = $this->getAvailableStore($storeId, $customer);

        $this->markAsReadForCustomer($store->getKey(), $customer->getKey());

        return $this
            ->httpResponse()
            ->setData($this->threadData($store, $customer));
    }

    public function archive(int|string $storeId): BaseHttpResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $customer = auth('customer')->user();
        $store = $this->getAvailableStore($storeId, $customer);

        $this->archiveThreadForCustomer($store->getKey(), $customer->getKey());

        return $this
            ->httpResponse()
            ->setNextUrl(route('customer.messages.index'))
            ->setData(['next_url' => route('customer.messages.index')])
            ->setMessage(__('Conversation archived.'));
    }

    public function unarchive(int|string $storeId): BaseHttpResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $customer = auth('customer')->user();
        $store = $this->getAvailableStore($storeId, $customer);

        $this->unarchiveThreadForCustomer($store->getKey(), $customer->getKey());

        return $this
            ->httpResponse()
            ->setNextUrl(route('customer.messages.show', $store->getKey()))
            ->setData(['next_url' => route('customer.messages.show', $store->getKey())])
            ->setMessage(__('Conversation reopened.'));
    }

    protected function getAvailableStore(int|string $storeId, Customer $customer): Store
    {
        $store = Store::query()->findOrFail($storeId);

        abort_if($customer->store?->id == $store->getKey(), 404);

        return $store;
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

    protected function markAsReadForCustomer(int $storeId, int $customerId): void
    {
        Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->where('sender_type', Message::SENDER_VENDOR)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    protected function threadData(Store $store, Customer $customer): array
    {
        $messages = $this->getConversationMessages($store->getKey(), $customer->getKey());

        return [
            'html' => view('plugins/marketplace::messages.partials.thread-items', [
                'messages' => $messages,
                'outgoingSenderType' => Message::SENDER_CUSTOMER,
            ])->render(),
            'last_message_id' => $messages->last()?->getKey(),
            'next_url' => route('customer.messages.show', $store->getKey()),
        ];
    }

    protected function isArchivedForCustomer(int $storeId, int $customerId): bool
    {
        return Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->whereNotNull('customer_archived_at')
            ->exists();
    }

    protected function archiveThreadForCustomer(int $storeId, int $customerId): void
    {
        Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->update(['customer_archived_at' => now()]);
    }

    protected function unarchiveThreadForCustomer(int $storeId, int $customerId): void
    {
        Message::query()
            ->where('store_id', $storeId)
            ->where('customer_id', $customerId)
            ->update(['customer_archived_at' => null]);
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
