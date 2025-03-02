<?php

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => [
                    'label' => 'lang:igniter::admin.button_save',
                    'class' => 'btn btn-primary',
                    'data-request' => 'onSave',
                    'data-progress-indicator' => 'igniter::admin.text_saving',
                ],
            ],
        ],
        'tabs' => [
            'fields' => [
                'guest_order' => [
                    'label' => 'lang:igniter.cart::default.label_guest_order',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_order',
                    'type' => 'switch',
                    'on' => 'lang:igniter::admin.text_yes',
                    'off' => 'lang:igniter::admin.text_no',
                    'comment' => 'lang:igniter.cart::default.help_guest_order',
                ],
                'location_order' => [
                    'label' => 'lang:igniter.cart::default.label_location_order',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_order',
                    'type' => 'switch',
                    'default' => false,
                    'on' => 'lang:igniter::admin.text_yes',
                    'off' => 'lang:igniter::admin.text_no',
                    'comment' => 'lang:igniter.cart::default.help_location_order',
                ],
                'order_email' => [
                    'label' => 'lang:igniter.cart::default.label_order_email',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_order',
                    'type' => 'checkboxtoggle',
                    'options' => [
                        'customer' => 'lang:igniter::system.settings.text_to_customer',
                        'admin' => 'lang:igniter::system.settings.text_to_admin',
                        'location' => 'lang:igniter::system.settings.text_to_location',
                    ],
                    'comment' => 'lang:igniter.cart::default.help_order_email',
                ],
                'default_order_status' => [
                    'label' => 'lang:igniter.cart::default.label_default_order_status',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_order',
                    'type' => 'selectlist',
                    'mode' => 'radio',
                    'options' => [\Igniter\Admin\Models\Status::class, 'getDropdownOptionsForOrder'],
                    'comment' => 'lang:igniter.cart::default.help_default_order_status',
                ],
                'processing_order_status' => [
                    'label' => 'lang:igniter.cart::default.label_processing_order_status',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_order',
                    'type' => 'selectlist',
                    'options' => [\Igniter\Admin\Models\Status::class, 'getDropdownOptionsForOrder'],
                    'comment' => 'lang:igniter.cart::default.help_processing_order_status',
                ],
                'completed_order_status' => [
                    'label' => 'lang:igniter.cart::default.label_completed_order_status',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_order',
                    'type' => 'selectlist',
                    'options' => [\Igniter\Admin\Models\Status::class, 'getDropdownOptionsForOrder'],
                    'comment' => 'lang:igniter.cart::default.help_completed_order_status',
                ],
                'canceled_order_status' => [
                    'label' => 'lang:igniter.cart::default.label_canceled_order_status',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_order',
                    'type' => 'selectlist',
                    'mode' => 'radio',
                    'options' => [\Igniter\Admin\Models\Status::class, 'getDropdownOptionsForOrder'],
                    'comment' => 'lang:igniter.cart::default.help_canceled_order_status',
                ],

                'invoice_prefix' => [
                    'label' => 'lang:igniter.cart::default.label_invoice_prefix',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_invoice',
                    'type' => 'text',
                    'span' => 'left',
                    'comment' => 'lang:igniter.cart::default.help_invoice_prefix',
                ],
                'invoice_logo' => [
                    'label' => 'lang:igniter.cart::default.label_invoice_logo',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_invoice',
                    'type' => 'mediafinder',
                    'span' => 'right',
                    'mode' => 'inline',
                    'comment' => 'lang:igniter.cart::default.help_invoice_logo',
                ],

                'tax_mode' => [
                    'label' => 'lang:igniter.cart::default.label_tax_mode',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_tax',
                    'type' => 'switch',
                    'default' => false,
                    'comment' => 'lang:igniter.cart::default.help_tax_mode',
                ],
                'tax_percentage' => [
                    'label' => 'lang:igniter.cart::default.label_tax_percentage',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_tax',
                    'type' => 'number',
                    'default' => 0,
                    'comment' => 'lang:igniter.cart::default.help_tax_percentage',
                ],
                'tax_menu_price' => [
                    'label' => 'lang:igniter.cart::default.label_tax_menu_price',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_tax',
                    'type' => 'select',
                    'options' => [
                        'lang:igniter.cart::default.text_menu_price_include_tax',
                        'lang:igniter.cart::default.text_apply_tax_on_menu_price',
                    ],
                    'comment' => 'lang:igniter.cart::default.help_tax_menu_price',
                ],
                'tax_delivery_charge' => [
                    'label' => 'lang:igniter.cart::default.label_tax_delivery_charge',
                    'tab' => 'lang:igniter.cart::default.text_tab_title_tax',
                    'type' => 'switch',
                    'on' => 'lang:igniter::admin.text_yes',
                    'off' => 'lang:igniter::admin.text_no',
                    'comment' => 'lang:igniter.cart::default.help_tax_delivery_charge',
                ],
            ],
        ],
    ],
];
