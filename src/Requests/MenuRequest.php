<?php

namespace Igniter\Cart\Requests;

use Igniter\System\Classes\FormRequest;

class MenuRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'menu_name' => lang('igniter::admin.label_name'),
            'menu_description' => lang('igniter::admin.label_description'),
            'menu_price' => lang('igniter.cart::default.menus.label_price'),
            'categories.*' => lang('igniter.cart::default.menus.label_category'),
            'ingredients.*' => lang('igniter.cart::default.menus.label_ingredients'),
            'mealtimes.*' => lang('igniter.cart::default.menus.label_mealtime'),
            'locations.*' => lang('igniter::admin.column_location'),
            'minimum_qty' => lang('igniter.cart::default.menus.label_minimum_qty'),
            'order_restriction.*' => lang('igniter.cart::default.menus.label_order_restriction'),
            'menu_status' => lang('igniter::admin.label_status'),
            'mealtime_id' => lang('igniter.cart::default.menus.label_mealtime'),
            'menu_priority' => lang('igniter.cart::default.menus.label_menu_priority'),
            'menu_option_values' => lang('igniter.cart::default.menu_options.label_option_value_id'),
        ];
    }

    public function rules()
    {
        return [
            'menu_name' => ['required', 'string', 'between:2,255'],
            'menu_description' => ['string', 'between:2,1028'],
            'menu_price' => ['required', 'numeric', 'min:0'],
            'categories.*' => ['sometimes', 'required', 'integer'],
            'ingredients.*' => ['sometimes', 'required', 'integer'],
            'mealtimes.*' => ['sometimes', 'required', 'integer'],
            'locations.*' => ['integer'],
            'minimum_qty' => ['sometimes', 'required', 'integer', 'min:1'],
            'order_restriction.*' => ['nullable', 'string'],
            'menu_status' => ['boolean'],
            'mealtime_id' => ['nullable', 'integer'],
            'menu_priority' => ['nullable', 'integer'],
        ];
    }
}
