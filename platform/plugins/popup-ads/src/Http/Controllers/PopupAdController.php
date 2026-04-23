<?php

namespace Botble\PopupAds\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\PopupAds\Forms\PopupAdForm;
use Botble\PopupAds\Http\Requests\PopupAdRequest;
use Botble\PopupAds\Models\PopupAd;
use Botble\PopupAds\Tables\PopupAdTable;
use Illuminate\Http\Request;

class PopupAdController extends BaseController
{
    public function index(PopupAdTable $table)
    {
        PageTitle::setTitle(trans('plugins/popup-ads::popup-ads.name'));

        return $table->renderTable();
    }

    public function create()
    {
        PageTitle::setTitle(trans('plugins/popup-ads::popup-ads.create'));

        return PopupAdForm::create()->renderForm();
    }

    public function store(PopupAdRequest $request, BaseHttpResponse $response)
    {
        $form = PopupAdForm::create()->setRequest($request);
        $form->save();

        return $response
            ->setPreviousUrl(route('popup-ads.index'))
            ->setNextUrl(route('popup-ads.edit', $form->getModel()->id))
            ->withCreatedSuccessMessage();
    }

    public function edit(PopupAd $popupAd, Request $request)
    {
        event(new BeforeEditContentEvent($request, $popupAd));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $popupAd->name]));

        return PopupAdForm::createFromModel($popupAd)->renderForm();
    }

    public function update(PopupAd $popupAd, PopupAdRequest $request, BaseHttpResponse $response)
    {
        PopupAdForm::createFromModel($popupAd)
            ->setRequest($request)
            ->save();

        return $response
            ->setPreviousUrl(route('popup-ads.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(PopupAd $popupAd)
    {
        return DeleteResourceAction::make($popupAd);
    }
}
