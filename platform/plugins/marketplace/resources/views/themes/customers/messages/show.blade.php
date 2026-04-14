@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Messages'))

@section('content')
    @include('plugins/marketplace::messages.partials.thread-styles')

    <div class="mb-3 d-flex justify-content-between align-items-start gap-3">
        <div>
            <h5 class="mb-1">{{ $store->name }}</h5>
            <p class="text-muted mb-0">{{ __('Replies from the store will appear here while this page is open.') }}</p>
        </div>

        <div class="d-flex gap-2">
            @if ($isArchived)
                <form method="POST" action="{{ route('customer.messages.unarchive', $store->getKey()) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">{{ __('Reopen') }}</button>
                </form>
            @else
                <form method="POST" action="{{ route('customer.messages.archive', $store->getKey()) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm">{{ __('Archive') }}</button>
                </form>
            @endif

            <a href="{{ route('customer.messages.index', ['tab' => $isArchived ? 'archived' : 'active']) }}" class="btn btn-outline-secondary btn-sm">
                {{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="marketplace-thread-wrapper" data-bb-toggle="marketplace-thread-wrapper" data-fetch-url="{{ route('customer.messages.refresh', $store->getKey()) }}">
        @include('plugins/marketplace::messages.partials.thread-items', [
            'messages' => $messages,
            'outgoingSenderType' => Botble\Marketplace\Models\Message::SENDER_CUSTOMER,
        ])

        @unless($isArchived)
            <div class="marketplace-thread__composer">
                <form method="POST" action="{{ route('customer.messages.store', $store->getKey()) }}" data-bb-toggle="marketplace-thread-form">
                    @csrf

                    <div class="mb-3">
                        <textarea class="form-control" name="content" rows="4" placeholder="{{ __('Type your message...') }}" required></textarea>
                    </div>

                    <button class="btn btn-primary" type="submit" data-bb-loading="button-loading">
                        {{ __('Send message') }}
                    </button>
                </form>
            </div>
        @endunless
    </div>

    @unless($isArchived)
        @include('plugins/marketplace::messages.partials.thread-script')
    @endunless
@endsection
