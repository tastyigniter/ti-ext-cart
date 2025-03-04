<?php

use Igniter\PayRegister\Models\Payment;

return [
    'form' => [
        'fields' => [
            'guest_order' => [
                'label' => 'lang:igniter.cart::default.label_guest_order',
                'accordion' => 'lang:igniter.local::default.text_tab_general_options',
                'type' => 'radiotoggle',
                'comment' => 'lang:igniter.local::default.help_guest_order',
                'default' => -1,
                'options' => [
                    -1 => 'lang:igniter::admin.text_use_default',
                    0 => 'lang:igniter::admin.text_no',
                    1 => 'lang:igniter::admin.text_yes',
                ],
            ],
            'limit_orders' => [
                'label' => 'lang:igniter.local::default.label_limit_orders',
                'accordion' => 'lang:igniter.local::default.text_tab_general_options',
                'type' => 'switch',
                'default' => 0,
                'comment' => 'lang:igniter.local::default.help_limit_orders',
                'span' => 'left',
            ],
            'limit_orders_count' => [
                'label' => 'lang:igniter.local::default.label_limit_orders_count',
                'accordion' => 'lang:igniter.local::default.text_tab_general_options',
                'type' => 'number',
                'default' => 50,
                'span' => 'right',
                'comment' => 'lang:igniter.local::default.help_limit_orders_interval',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'limit_orders',
                    'condition' => 'checked',
                ],
            ],
            'payments' => [
                'label' => 'lang:igniter.payregister::default.label_payments',
                'accordion' => 'lang:igniter.local::default.text_tab_general_options',
                'type' => 'checkboxlist',
                'options' => [Payment::class, 'listDropdownOptions'],
                'commentAbove' => 'lang:igniter.payregister::default.help_payments',
                'placeholder' => 'lang:igniter.payregister::default.help_no_payments',
            ],
        ],
    ],
];