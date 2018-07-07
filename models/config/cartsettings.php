<?php

/**
 * Model configuration options for settings model.
 */

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save'      => ['label' => 'lang:admin::lang.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label'             => 'lang:admin::lang.button_save_close',
                    'class'             => 'btn btn-default',
                    'data-request'      => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'fields'  => [
            'conditions' => [
                'label'            => 'lang:sampoyigi.cart::default.label_cart_conditions',
                'type'             => 'repeater',
                'sortable'         => TRUE,
                'showAddButton'    => FALSE,
                'showRemoveButton' => FALSE,
                'commentAbove'     => 'lang:sampoyigi.cart::default.help_cart_conditions',
                'form'             => [
                    'fields' => [
                        'priority' => [
                            'label' => 'lang:sampoyigi.cart::default.column_condition_priority',
                            'type'  => 'hidden',
                        ],
                        'name'     => [
                            'label'      => 'lang:sampoyigi.cart::default.column_condition_name',
                            'type'       => 'text',
                            'attributes' => [
                                'readonly' => TRUE,
                            ],
                        ],
                        'label'    => [
                            'label' => 'lang:sampoyigi.cart::default.column_condition_title',
                            'type'  => 'text',
                        ],
                        'status'   => [
                            'label' => 'lang:sampoyigi.cart::default.column_condition_status',
                            'type'  => 'switch',
                        ],
                    ],
                ],
            ],
        ],
    ],
];