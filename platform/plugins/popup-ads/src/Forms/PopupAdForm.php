<?php

namespace Botble\PopupAds\Forms;

use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\SortOrderFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\PopupAds\Http\Requests\PopupAdRequest;
use Botble\PopupAds\Models\PopupAd;

class PopupAdForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(PopupAd::class)
            ->setValidatorClass(PopupAdRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->add('image', MediaImageField::class, MediaImageFieldOption::make()->required())
            ->add('title', TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/popup-ads::popup-ads.fields.title'))
                    ->placeholder(trans('plugins/popup-ads::popup-ads.fields.title'))
                    ->helperText(trans('plugins/popup-ads::popup-ads.fields.title_helper'))
            )
            ->add('description', TextareaField::class, [
                'label'   => trans('plugins/popup-ads::popup-ads.fields.description'),
                'attr'    => [
                    'rows'        => 3,
                    'placeholder' => trans('plugins/popup-ads::popup-ads.fields.description'),
                ],
            ])
            ->add('url', TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/popup-ads::popup-ads.fields.url'))
                    ->placeholder('https://')
            )
            ->add('open_in_new_tab', OnOffField::class, [
                'label'         => trans('plugins/popup-ads::popup-ads.fields.open_in_new_tab'),
                'default_value' => true,
            ])
            ->add('delay_seconds', NumberField::class, [
                'label'         => trans('plugins/popup-ads::popup-ads.fields.delay_seconds'),
                'default_value' => 3,
                'attr'          => ['min' => 0, 'max' => 3600],
                'help_block'    => ['text' => trans('plugins/popup-ads::popup-ads.fields.delay_seconds_helper')],
            ])
            ->add('dismiss_duration', SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/popup-ads::popup-ads.fields.dismiss_duration'))
                    ->choices(PopupAd::getDismissDurationOptions())
                    ->selected($this->getModel()->dismiss_duration ?? '1_day')
            )
            ->add('started_at', DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/popup-ads::popup-ads.fields.started_at'))
                    ->helperText(trans('plugins/popup-ads::popup-ads.fields.started_at_helper'))
            )
            ->add('ended_at', DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/popup-ads::popup-ads.fields.ended_at'))
                    ->helperText(trans('plugins/popup-ads::popup-ads.fields.ended_at_helper'))
            )
            ->add('order', NumberField::class, SortOrderFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}
