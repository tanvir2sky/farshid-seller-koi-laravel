@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>
                {{ $feedPin->exists ? trans('core/base::forms.edit_item', ['name' => '#' . $feedPin->getKey()]) : trans('plugins/marketplace::feed-pin.create') }}
            </x-core::card.title>
        </x-core::card.header>
        <x-core::card.body>
            {!! $form !!}
        </x-core::card.body>
    </x-core::card>
@stop
