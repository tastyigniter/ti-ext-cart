<?php

namespace Igniter\Cart\Http\Requests;

use Igniter\System\Classes\FormRequest;

class OrderSettingsRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'order_email.*' => lang('igniter.cart::default.label_order_email'),
            'processing_order_status' => lang('igniter.cart::default.label_processing_order_status'),
            'completed_order_status' => lang('igniter.cart::default.label_completed_order_status'),
            'canceled_order_status' => lang('igniter.cart::default.label_canceled_order_status'),
            'guest_order' => lang('igniter.cart::default.label_guest_order'),
            'location_order' => lang('igniter.cart::default.label_location_order'),

            'invoice_prefix' => lang('igniter.cart::default.label_invoice_prefix'),
            'invoice_logo' => lang('igniter.cart::default.label_invoice_logo'),

            'tax_mode' => lang('igniter.cart::default.label_tax_mode'),
            'tax_title' => lang('igniter.cart::default.label_tax_title'),
            'tax_percentage' => lang('igniter.cart::default.label_tax_percentage'),
            'tax_menu_price' => lang('igniter.cart::default.label_tax_menu_price'),
            'tax_delivery_charge' => lang('igniter.cart::default.label_tax_delivery_charge'),
        ];
    }

    public function rules()
    {
        return [
            'order_email.*' => ['required', 'alpha'],
            'processing_order_status' => ['required', 'array'],
            'completed_order_status' => ['required', 'array'],
            'processing_order_status.*' => ['required', 'integer'],
            'completed_order_status.*' => ['required', 'integer'],
            'canceled_order_status' => ['required', 'integer'],
            'guest_order' => ['required', 'integer'],
            'location_order' => ['required', 'integer'],

            'invoice_prefix' => ['nullable', 'regex:/^[a-zA-Z0-9-_\{\}]+$/'],
            'invoice_logo' => ['nullable', 'string'],

            'tax_mode' => ['required', 'integer'],
            'tax_title' => ['string', 'max:32'],
            'tax_percentage' => ['required_if:tax_mode,1', 'numeric'],
            'tax_menu_price' => ['required', 'numeric'],
            'tax_delivery_charge' => ['numeric'],
        ];
    }
}
