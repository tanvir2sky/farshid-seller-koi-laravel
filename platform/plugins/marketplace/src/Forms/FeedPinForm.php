<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\Fields\DatetimeField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Marketplace\Enums\FeedPinTypeEnum;
use Botble\Marketplace\Http\Requests\FeedPinRequest;
use Botble\Marketplace\Models\FeedPin;

class FeedPinForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(FeedPin::class)
            ->setValidatorClass(FeedPinRequest::class)
            ->template('core/base::forms.form-no-wrap')
            ->add(
                'pin_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/marketplace::feed-pin.form.pin_type'))
                    ->choices(FeedPinTypeEnum::labels())
                    ->required()
            )
            ->add('target_id', NumberField::class, [
                'label' => trans('plugins/marketplace::feed-pin.form.target_id'),
                'required' => true,
                'attr' => ['min' => 1],
            ])
            ->add('priority', NumberField::class, [
                'label' => trans('plugins/marketplace::feed-pin.form.priority'),
                'required' => true,
                'attr' => ['min' => 0, 'max' => 9999],
            ])
            ->add('starts_at', DatetimeField::class, [
                'label' => trans('plugins/marketplace::feed-pin.form.starts_at'),
            ])
            ->add('ends_at', DatetimeField::class, [
                'label' => trans('plugins/marketplace::feed-pin.form.ends_at'),
            ])
            ->add(
                'is_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/marketplace::feed-pin.form.is_enabled'))
                    ->value($this->getModel()->getKey() ? $this->getModel()->is_enabled : true)
            );
    }
}
