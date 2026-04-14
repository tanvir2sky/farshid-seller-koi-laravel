@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Messages'))

@section('content')
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'active' ? 'active' : '' }}" href="{{ route('customer.messages.index', ['tab' => 'active']) }}">
                {{ __('Active') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'archived' ? 'active' : '' }}" href="{{ route('customer.messages.index', ['tab' => 'archived']) }}">
                {{ __('Archived') }}
            </a>
        </li>
    </ul>

    <div class="list-group">
        @forelse ($threads as $thread)
            <a href="{{ route('customer.messages.show', $thread->store_id) }}" class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold">{{ $thread->store->name }}</div>
                        <div class="text-muted small mt-1">{{ Str::limit($thread->content, 110) }}</div>
                    </div>

                    <div class="text-end">
                        <div class="small text-muted">{{ BaseHelper::formatDateTime($thread->created_at) }}</div>

                        @if (($unreadCounts[$thread->store_id] ?? 0) > 0)
                            <span class="badge bg-primary mt-2">{{ $unreadCounts[$thread->store_id] }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="alert alert-info mb-0">
                {{ $tab === 'archived' ? __('No archived conversations yet.') : __('You have no messages yet.') }}
            </div>
        @endforelse
    </div>

    @if ($threads->hasPages())
        <div class="mt-3">
            {!! $threads->appends(['tab' => $tab])->links() !!}
        </div>
    @endif
@endsection
