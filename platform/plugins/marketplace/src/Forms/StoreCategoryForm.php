<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Marketplace\Http\Requests\StoreCategoryRequest;
use Botble\Marketplace\Models\StoreCategory;
use Botble\Marketplace\Supports\StoreCategoryHelper;

class StoreCategoryForm extends FormAbstract
{
    public function setup(): void
    {
        $categories = StoreCategoryHelper::getTreeCategoriesOptions(
            StoreCategoryHelper::getTreeCategories(),
        );

        $categories = [0 => trans('plugins/marketplace::store-category.none')] + $categories;

        $maxOrder = StoreCategory::query()
            ->where(function ($query): void {
                $query->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->latest('order')
            ->value('order');

        $this
            ->model(StoreCategory::class)
            ->setValidatorClass(StoreCategoryRequest::class)
            ->template('core/base::forms.form-no-wrap')
            ->hasFiles()
            ->add('order', 'hidden', [
                'value' => $this->getModel()->exists ? $this->getModel()->order : (int) $maxOrder + 1,
            ])
            ->add('name', TextField::class, NameFieldOption::make())
            ->add(
                'parent_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('core/base::forms.parent'))
                    ->choices($categories)
                    ->searchable()
            )
            ->add(
                'description',
                EditorField::class,
                ContentFieldOption::make()->label(trans('core/base::forms.description'))
            )
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->add('image', MediaImageField::class, MediaImageFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}
