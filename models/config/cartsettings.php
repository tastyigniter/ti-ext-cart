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
                'default' => FALSE,
            ],
            'destroy_on_logout' => [
                'label' => 'lang:igniter.cart::default.label_destroy_on_logout',
                'type' => 'switch',
                'default' => FALSE,
            ],      
            'conditions' => [
                'label' => 'lang:igniter.cart::default.label_cart_conditions',
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
                'label' => 'lang:igniter.cart::default.label_enable_tipping',
	            'type' => 'radio',
	            'default' => false,
	            'options' => [
	                true => 'lang:admin::lang.text_yes',
	                false => 'lang:admin::lang.text_no',
	            ],
            ],     
            'tip_percentages' => [
                'label' => 'lang:igniter.cart::default.label_tip_percentages',
                'type' => 'repeater',
                'sortable' => TRUE,
                'showAddButton' => TRUE,
                'showRemoveButton' => TRUE,
                'form' => [
                    'fields' => [
                        'priority' => [
                            'label' => 'lang:igniter.cart::default.column_condition_priority',
                            'type' => 'hidden',
                        ],
                        'label' => [
                            'label' => 'lang:igniter.cart::default.column_percentage',
                            'type' => 'text',
                        ],
                        'amount' => [
                            'label' => 'lang:igniter.cart::default.column_amount',
                            'type' => 'text',
                        ]
                    ],
                ],
	            'trigger' => [
	                'action' => 'show',
	                'field' => 'enable_tipping',
	                'condition' => 'value[1]',
	            ],
            ],
        ],
    ],
];