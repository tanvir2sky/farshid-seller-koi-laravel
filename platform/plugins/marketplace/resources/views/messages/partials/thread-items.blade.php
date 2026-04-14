<div class="marketplace-thread__messages" data-bb-toggle="marketplace-thread-messages">
    @forelse ($messages as $message)
        @php
            $isOutgoing = isset($outgoingSenderType) && $outgoingSenderType === $message->sender_type;
        @endphp

        <div @class([
            'marketplace-thread__message',
            'marketplace-thread__message--outgoing' => $isOutgoing,
            'marketplace-thread__message--incoming' => ! $isOutgoing,
        ])>
            <div class="marketplace-thread__meta">
                <span>{{ $message->name }}</span>
                <span>{{ BaseHelper::formatDateTime($message->created_at) }}</span>
            </div>

            <div class="marketplace-thread__bubble">
                {!! BaseHelper::clean(nl2br($message->content)) !!}
            </div>
        </div>
    @empty
        <p class="text-muted mb-0">{{ __('No messages yet.') }}</p>
    @endforelse
</div>
