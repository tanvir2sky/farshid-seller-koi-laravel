@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @include('plugins/marketplace::messages.partials.thread-styles')

    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>{{ $seedMessage->store->name }}</x-core::card.title>

            <x-core::card.actions>
                <x-core::button tag="a" :href="route('marketplace.messages.index')" icon="ti ti-arrow-left">
                    {{ __('Back') }}
                </x-core::button>
            </x-core::card.actions>
        </x-core::card.header>

        <x-core::card.body>
            <div class="mb-3">
                <div class="text-muted">{{ __('Customer') }}: {{ $seedMessage->name }}</div>
                <div class="text-muted">{{ __('Email') }}: <a href="mailto:{{ $seedMessage->email }}">{{ $seedMessage->email }}</a></div>
            </div>

            <div class="marketplace-thread-wrapper">
                @include('plugins/marketplace::messages.partials.thread-items', [
                    'messages' => $messages,
                    'outgoingSenderType' => Botble\Marketplace\Models\Message::SENDER_VENDOR,
                ])
            </div>
        </x-core::card.body>
    </x-core::card>
@endsection
