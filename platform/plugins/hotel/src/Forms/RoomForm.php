<?php

namespace Botble\Hotel\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Hotel\Http\Requests\RoomRequest;
use Botble\Hotel\Models\Room;
use Botble\Hotel\Models\RoomCategory;
use Botble\Hotel\Models\Tax;

class RoomForm extends FormAbstract
{
    public function setup(): void
    {
        Assets::usingVueJS()
            ->addScripts(['input-mask'])
            ->addStylesDirectly('vendor/core/plugins/hotel/css/hotel.css');

        $roomCategories = RoomCategory::query()->pluck('name', 'id')->all();
        $taxes = Tax::query()->pluck('title', 'id')->all();
        $model = $this->getModel();
        $room = $model instanceof Room ? $model : null;

        $this
            ->setupModel(new Room())
            ->setValidatorClass(RoomRequest::class)
            ->withCustomFields()
            ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->toArray())
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('price', 'text', [
                'label' => trans('plugins/hotel::room.form.price'),
                'required' => true,
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
                'attr' => [
                    'id' => 'price-number',
                    'placeholder' => trans('plugins/hotel::room.form.price'),
                    'class' => 'form-control input-mask-number',
                ],
            ])
            ->add('number_of_rooms', 'text', [
                'label' => trans('plugins/hotel::room.form.number_of_rooms'),
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
                'attr' => [
                    'id' => 'number-of-rooms-number',
                    'placeholder' => trans('plugins/hotel::room.form.number_of_rooms'),
                    'class' => 'form-control input-mask-number',
                ],
                'default_value' => 1,
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('max_adults', 'text', [
                'label' => trans('plugins/hotel::room.form.max_adults'),
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
                'attr' => [
                    'id' => 'max-adults-number',
                    'placeholder' => trans('plugins/hotel::room.form.max_adults'),
                    'class' => 'form-control input-mask-number',
                ],
                'default_value' => 1,
            ])
            ->add('max_children', 'text', [
                'label' => trans('plugins/hotel::room.form.max_children'),
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
                'attr' => [
                    'id' => 'max-children-number',
                    'placeholder' => trans('plugins/hotel::room.form.max_children'),
                    'class' => 'form-control input-mask-number',
                ],
                'default_value' => 0,
            ])
            ->add('rowClose2', 'html', [
                'html' => '</div>',
            ])
            ->add('images[]', 'mediaImages', [
                'label' => trans('plugins/hotel::room.images'),
                'values' => $room?->id ? $room->images : [],
            ])
            ->add('status', SelectField::class, StatusFieldOption::make()->toArray())
            ->add('room_category_id', 'customSelect', [
                'label' => trans('plugins/hotel::room.form.category'),
                'required' => true,
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => $roomCategories,
                'selected' => $room?->room_category_id ?: array_key_first($roomCategories),
            ])
            ->add('tax_id', 'customSelect', [
                'label' => trans('plugins/hotel::room.form.tax'),
                'required' => true,
                'wrapper' => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => $taxes,
                'selected' => $room?->tax_id ?: array_key_first($taxes),
            ])
            ->setBreakFieldPoint('status');
    }
}
