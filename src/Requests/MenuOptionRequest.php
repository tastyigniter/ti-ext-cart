<?php

namespace Igniter\Cart\Requests;

use Igniter\System\Classes\FormRequest;

class MenuOptionRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'option_name' => lang('igniter.cart::default.menu_options.label_option_group_name'),
            'display_type' => lang('igniter.cart::default.menu_options.label_display_type'),
            'is_required' => lang('igniter.cart::default.menu_options.label_option_required'),
            'min_selected' => lang('igniter.cart::default.menu_options.label_min_selected'),
            'max_selected' => lang('igniter.cart::default.menu_options.label_max_selected'),
            'locations.*' => lang('igniter::admin.label_location'),
            'option_values' => lang('igniter.cart::default.menu_options.label_option_values'),
        ];
    }

    public function rules()
    {
        return [
            'option_name' => ['required', 'string', 'min:2', 'max:32'],
            'display_type' => ['required', 'alpha'],
            'is_required' => ['boolean'],
            'min_selected' => ['integer', 'lte:max_selected'],
            'max_selected' => ['integer', 'gte:min_selected'],
            'locations' => ['array'],
            'locations.*' => ['integer'],
            'option_values' => ['required', 'array'],
        ];
    }
}
