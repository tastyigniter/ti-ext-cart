<?php

namespace Igniter\Cart\Http\Requests;

use Igniter\System\Classes\FormRequest;

class DeliverySettingsRequest extends FormRequest
{
    public function attributes(): array
    {
        return [
            'is_enabled' => lang('igniter.local::default.label_is_enabled'),
            'delivery_time_interval' => lang('igniter.local::default.label_delivery_time_interval'),
            'delivery_lead_time' => lang('igniter.local::default.label_delivery_lead_time'),
            'future_orders.enable_delivery' => lang('igniter.local::default.label_future_delivery_order'),
            'future_orders.min_delivery_days' => lang('igniter.local::default.label_future_min_delivery_days'),
            'future_orders.delivery_days' => lang('igniter.local::default.label_future_delivery_days'),
            'delivery_time_restriction' => lang('igniter.local::default.label_delivery_time_restriction'),
            'delivery_cancellation_timeout' => lang('igniter.local::default.label_delivery_cancellation_timeout'),
            'delivery_add_lead_time' => lang('igniter.local::default.label_delivery_add_lead_time'),
            'delivery_min_order_amount' => lang('igniter.local::default.label_delivery_min_order_amount'),
        ];
    }

    public function rules(): array
    {
        return [
            'is_enabled' => ['boolean'],
            'delivery_time_interval' => ['integer', 'min:5'],
            'delivery_lead_time' => ['integer', 'min:5'],
            'future_orders.enable_delivery' => ['boolean'],
            'future_orders.min_delivery_days' => ['integer', 'min:0'],
            'future_orders.delivery_days' => ['integer', 'min:0', 'gt:future_orders.min_delivery_days'],
            'delivery_time_restriction' => ['nullable', 'integer', 'max:2'],
            'delivery_add_lead_time' => ['boolean'],
            'delivery_cancellation_timeout' => ['integer', 'min:0', 'max:999'],
            'delivery_min_order_amount' => ['numeric', 'min:0'],
        ];
    }
}
