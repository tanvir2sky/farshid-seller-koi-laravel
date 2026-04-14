@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="row row-cards">
        <div class="col-12">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>{{ __('Conversations') }}</x-core::card.title>
                </x-core::card.header>

                <x-core::card.body class="p-0">
                    <div class="p-3 pb-0">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'active' ? 'active' : '' }}" href="{{ route('marketplace.vendor.messages.index', ['tab' => 'active']) }}">
                                    {{ __('Active') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $tab === 'archived' ? 'active' : '' }}" href="{{ route('marketplace.vendor.messages.index', ['tab' => 'archived']) }}">
                                    {{ __('Archived') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse ($threads as $thread)
                            <a href="{{ route('marketplace.vendor.messages.show', $thread->getKey()) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $thread->customer->name }}</div>
                                        <div class="text-muted small">{{ $thread->customer->email }}</div>
                                        <div class="small mt-2">{{ Str::limit($thread->content, 120) }}</div>
                                    </div>

                                    <div class="text-end">
                                        <div class="small text-muted">{{ BaseHelper::formatDateTime($thread->created_at) }}</div>

                                        @if (($unreadCounts[$thread->customer_id] ?? 0) > 0)
                                            <span class="badge bg-primary mt-2">{{ $unreadCounts[$thread->customer_id] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-3 text-muted">
                                {{ $tab === 'archived' ? __('No archived customer conversations yet.') : __('No customer conversations yet.') }}
                            </div>
                        @endforelse
                    </div>
                </x-core::card.body>
            </x-core::card>

            @if ($threads->hasPages())
                <div class="mt-3">
                    {!! $threads->appends(['tab' => $tab])->links() !!}
                </div>
            @endif
        </div>

        <div class="col-12">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>{{ __('Guest messages') }}</x-core::card.title>
                </x-core::card.header>

                <x-core::card.body class="p-0">
                    <div class="list-group list-group-flush">
                        @forelse ($guestMessages as $message)
                            <a href="{{ route('marketplace.vendor.messages.show', $message->getKey()) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $message->name }}</div>
                                        <div class="text-muted small">{{ $message->email }}</div>
                                        <div class="small mt-2">{{ Str::limit($message->content, 120) }}</div>
                                    </div>

                                    <div class="small text-muted text-end">{{ BaseHelper::formatDateTime($message->created_at) }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="p-3 text-muted">{{ __('No guest messages yet.') }}</div>
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
@stop
