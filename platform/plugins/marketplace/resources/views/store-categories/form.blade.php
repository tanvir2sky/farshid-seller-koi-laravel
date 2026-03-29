@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>
                {{ $storeCategory->exists ? trans('core/base::forms.edit_item', ['name' => $storeCategory->name]) : trans('plugins/marketplace::store-category.create') }}
            </x-core::card.title>
        </x-core::card.header>
        <x-core::card.body>
            {!! $form !!}
        </x-core::card.body>
    </x-core::card>
@stop
