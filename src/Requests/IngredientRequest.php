<?php

namespace Igniter\Cart\Requests;

use Igniter\System\Classes\FormRequest;

class IngredientRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'name' => lang('igniter::admin.label_name'),
            'description' => lang('igniter::admin.label_description'),
            'status' => lang('igniter::admin.label_status'),
            'is_allergen' => lang('igniter.cart::default.ingredients.label_allergen'),
        ];
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'between:2,255'],
            'description' => ['string', 'min:2'],
            'status' => ['boolean'],
            'is_allergen' => ['boolean'],
        ];
    }
}
