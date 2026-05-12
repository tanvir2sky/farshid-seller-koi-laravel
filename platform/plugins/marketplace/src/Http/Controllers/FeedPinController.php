<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Forms\FeedPinForm;
use Botble\Marketplace\Http\Requests\FeedPinRequest;
use Botble\Marketplace\Models\FeedPin;
use Botble\Marketplace\Tables\FeedPinTable;

class FeedPinController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/marketplace::feed-pin.name'), route('marketplace.feed-pins.index'));
    }

    public function index(FeedPinTable $table)
    {
        $this->pageTitle(trans('plugins/marketplace::feed-pin.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/marketplace::feed-pin.create'));

        $feedPin = new FeedPin();

        return view('plugins/marketplace::feed-pins.form', [
            'feedPin' => $feedPin,
            'form' => FeedPinForm::create()
                ->setUrl(route('marketplace.feed-pins.create.store'))
                ->renderForm(),
        ]);
    }

    public function store(FeedPinRequest $request)
    {
        $data = $request->validated();
        $data['is_enabled'] = $request->boolean('is_enabled');

        $feedPin = FeedPin::query()->create($data);

        event(new CreatedContentEvent(FEED_PIN_MODULE_SCREEN_NAME, $request, $feedPin));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.feed-pins.index'))
            ->setNextUrl(route('marketplace.feed-pins.edit', $feedPin))
            ->withCreatedSuccessMessage();
    }

    public function edit(FeedPin $feedPin)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => '#' . $feedPin->getKey()]));

        return view('plugins/marketplace::feed-pins.form', [
            'feedPin' => $feedPin,
            'form' => FeedPinForm::createFromModel($feedPin)
                ->setUrl(route('marketplace.feed-pins.edit.update', $feedPin))
                ->renderForm(),
        ]);
    }

    public function update(FeedPin $feedPin, FeedPinRequest $request)
    {
        $data = $request->validated();
        $data['is_enabled'] = $request->boolean('is_enabled');

        $feedPin->update($data);

        event(new UpdatedContentEvent(FEED_PIN_MODULE_SCREEN_NAME, $request, $feedPin));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.feed-pins.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(FeedPin $feedPin)
    {
        return DeleteResourceAction::make($feedPin);
    }
}
