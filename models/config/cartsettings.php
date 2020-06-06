<?php

/**
 * Model configuration options for settings model.
 */

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => ['label' => 'lang:admin::lang.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'fields' => [
            'abandoned_cart' => [
                'label' => 'lang:igniter.cart::default.label_abandoned_cart',
                'type' => 'switch',
                'span' => 'left',
                'default' => FALSE,
            ],
            'destroy_on_logout' => [
                'label' => 'lang:igniter.cart::default.label_destroy_on_logout',
                'type' => 'switch',
                'span' => 'right',
                'default' => FALSE,
            ],
        ],
        'tabs' => [
            'fields' => [
                'conditions' => [
                    'tab' => 'lang:igniter.cart::default.label_cart_conditions',
                    'type' => 'repeater',
                    'sortable' => TRUE,
                    'showAddButton' => FALSE,
                    'showRemoveButton' => FALSE,
                    'commentAbove' => 'lang:igniter.cart::default.help_cart_conditions',
                    'form' => [
                        'fields' => [
                            'priority' => [
                                'label' => 'lang:igniter.cart::default.column_condition_priority',
                                'type' => 'hidden',
                            ],
                            'name' => [
                                'label' => 'lang:igniter.cart::default.column_condition_name',
                                'type' => 'text',
                                'attributes' => [
                                    'readonly' => TRUE,
                                ],
                            ],
                            'label' => [
                                'label' => 'lang:igniter.cart::default.column_condition_title',
                                'type' => 'text',
                            ],
                            'status' => [
                                'label' => 'lang:admin::lang.label_status',
                                'type' => 'switch',
                                'default' => TRUE,
                            ],
                        ],
                    ],
                ],
                'enable_tipping' => [
                    'tab' => 'lang:igniter.cart::default.label_tipping',
                    'label' => 'lang:igniter.cart::default.label_enable_tipping',
                    'type' => 'switch',
                    'default' => FALSE,
                    'on' => 'lang:admin::lang.text_yes',
                    'off' => 'lang:admin::lang.text_no',
                ],
                'tip_value_type' => [
                    'tab' => 'lang:igniter.cart::default.label_tipping',
                    'label' => 'lang:igniter.cart::default.label_tip_value_type',
                    'type' => 'radiotoggle',
                    'default' => 'F',
                    'options' => [
                        'F' => 'lang:admin::lang.coupons.text_fixed_amount',
                        'P' => 'lang:admin::lang.coupons.text_percentage',
                    ],
                    'trigger' => [
                        'action' => 'show',
                        'field' => 'enable_tipping',
                        'condition' => 'checked',
                    ],
                ],
                'tip_amounts' => [
                    'tab' => 'lang:igniter.cart::default.label_tipping',
                    'label' => 'lang:igniter.cart::default.label_tip_amounts',
                    'type' => 'repeater',
                    'span' => 'left',
                    'sortable' => TRUE,
                    'showAddButton' => TRUE,
                    'showRemoveButton' => TRUE,
                    'form' => [
                        'fields' => [
                            'priority' => [
                                'label' => 'lang:igniter.cart::default.column_condition_priority',
                                'type' => 'hidden',
                            ],
                            'value' => [
                                'label' => 'lang:igniter.cart::default.column_tip_amount',
                                'type' => 'money',
                            ],
                        ],
                    ],
                    'trigger' => [
                        'action' => 'show',
                        'field' => 'enable_tipping',
                        'condition' => 'checked',
                    ],
                ],
            ],
        ],
    ],
];