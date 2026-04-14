@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row row-cards">
        <div class="col-12">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>{{ __('Customer conversations') }}</x-core::card.title>
                </x-core::card.header>

                <x-core::card.body class="p-0">
                    <div class="list-group list-group-flush">
                        @forelse ($threads as $thread)
                            <a href="{{ route('marketplace.messages.show', $thread->getKey()) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $thread->store->name }} / {{ $thread->customer->name }}</div>
                                        <div class="text-muted small">{{ $thread->customer->email }}</div>
                                        <div class="small mt-2">{{ Str::limit($thread->content, 130) }}</div>
                                    </div>

                                    <div class="small text-muted text-end">{{ BaseHelper::formatDateTime($thread->created_at) }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="p-3 text-muted">{{ __('No conversations found.') }}</div>
                        @endforelse
                    </div>
                </x-core::card.body>
            </x-core::card>

            @if ($threads->hasPages())
                <div class="mt-3">
                    {!! $threads->links() !!}
                </div>
            @endif
        </div>

        <div class="col-12">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>{{ __('Guest contact messages') }}</x-core::card.title>
                </x-core::card.header>

                <x-core::card.body class="p-0">
                    <div class="list-group list-group-flush">
                        @forelse ($guestMessages as $message)
                            <a href="{{ route('marketplace.messages.show', $message->getKey()) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $message->store->name }} / {{ $message->name }}</div>
                                        <div class="text-muted small">{{ $message->email }}</div>
                                        <div class="small mt-2">{{ Str::limit($message->content, 130) }}</div>
                                    </div>

                                    <div class="small text-muted text-end">{{ BaseHelper::formatDateTime($message->created_at) }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="p-3 text-muted">{{ __('No guest messages found.') }}</div>
                        @endforelse
                    </div>
                </x-core::card.body>
            </x-core::card>

            @if ($guestMessages->hasPages())
                <div class="mt-3">
                    {!! $guestMessages->appends(request()->except('guest_page'))->links() !!}
                </div>
            @endif
        </div>
    </div>
@endsection
