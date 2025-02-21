<?php

declare(strict_types=1);

namespace Igniter\Cart\Http\Requests;

use Igniter\System\Classes\FormRequest;

class CheckoutSettingsRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'guest_order' => lang('igniter.cart::default.label_guest_order'),
            'limit_orders' => lang('igniter.local::default.label_limit_orders'),
            'limit_orders_count' => lang('igniter.local::default.label_limit_orders_count'),
            'payments.*' => lang('igniter.payregister::default.label_payments'),
        ];
    }

    public function rules(): array
    {
        return [
            'guest_order' => ['integer'],
            'limit_orders' => ['boolean'],
            'limit_orders_count' => ['integer', 'min:1', 'max:999'],
            'payments.*' => ['string'],
        ];
    }
}
