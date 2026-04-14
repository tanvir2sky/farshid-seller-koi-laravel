@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include('plugins/marketplace::messages.partials.thread-styles')

    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>{{ $seedMessage->name }}</x-core::card.title>

            <x-core::card.actions>
                @if ($canReply)
                    @if ($isArchived)
                        <form method="POST" action="{{ route('marketplace.vendor.messages.unarchive', $seedMessage->getKey()) }}" class="d-inline-block me-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm">{{ __('Reopen') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('marketplace.vendor.messages.archive', $seedMessage->getKey()) }}" class="d-inline-block me-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning btn-sm">{{ __('Archive') }}</button>
                        </form>
                    @endif
                @endif

                <x-core::button tag="a" :href="route('marketplace.vendor.messages.index', ['tab' => $isArchived ? 'archived' : 'active'])" icon="ti ti-arrow-left">
                    {{ __('Back') }}
                </x-core::button>
            </x-core::card.actions>
        </x-core::card.header>

        <x-core::card.body>
            <div class="mb-3">
                <div class="text-muted">{{ __('Email') }}: <a href="mailto:{{ $seedMessage->email }}">{{ $seedMessage->email }}</a></div>
                <div class="text-muted">{{ __('Started at') }}: {{ BaseHelper::formatDateTime($seedMessage->created_at) }}</div>
            </div>

            <div class="marketplace-thread-wrapper" data-bb-toggle="marketplace-thread-wrapper" data-fetch-url="{{ route('marketplace.vendor.messages.refresh', $seedMessage->getKey()) }}">
                @include('plugins/marketplace::messages.partials.thread-items', [
                    'messages' => $messages,
                    'outgoingSenderType' => Botble\Marketplace\Models\Message::SENDER_VENDOR,
                ])

                @if ($canReply && ! $isArchived)
                    <div class="marketplace-thread__composer">
                        <form method="POST" action="{{ route('marketplace.vendor.messages.reply', $seedMessage->getKey()) }}" data-bb-toggle="marketplace-thread-form">
                            @csrf

                            <div class="mb-3">
                                <textarea class="form-control" name="content" rows="4" placeholder="{{ __('Type your reply...') }}" required></textarea>
                            </div>

                            <button class="btn btn-primary" type="submit" data-bb-loading="button-loading">
                                {{ __('Send reply') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </x-core::card.body>
    </x-core::card>

    @if ($canReply && ! $isArchived)
        @include('plugins/marketplace::messages.partials.thread-script')
    @endif
@stop
