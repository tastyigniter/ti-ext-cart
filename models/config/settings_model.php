<?php

/**
 * Model configuration options for settings model.
 */

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save'      => ['label' => 'lang:admin::default.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label'             => 'lang:admin::default.button_save_close',
                    'class'             => 'btn btn-default',
                    'data-request'      => 'onSave',
                    'data-request-data' => 'close:1',
                ],
                'back'      => ['label' => 'lang:admin::default.button_icon_back', 'class' => 'btn btn-default', 'href' => 'settings'],
            ],
        ],
        'fields'  => [
//            'show_cart_images' => [
//                'label' => 'lang:sampoyigi.cart::default.label_show_cart_images',
//                'type'  => 'switch',
//            ],
//            'cart_images_h'    => [
//                'label' => 'lang:sampoyigi.cart::default.label_cart_images_h',
//                'span'  => 'left',
//                'type'  => 'number',
//                'trigger'  => [
//                    'action'    => 'show',
//                    'field'     => 'show_cart_images',
//                    'condition' => 'checked',
//                ],
//            ],
//            'cart_images_w'    => [
//                'label' => 'lang:sampoyigi.cart::default.label_cart_images_w',
//                'span'  => 'right',
//                'type'  => 'number',
//                'trigger'  => [
//                    'action'    => 'show',
//                    'field'     => 'show_cart_images',
//                    'condition' => 'checked',
//                ],
//            ],
//            'stock_checkout'     => [
//                'label'   => 'lang:sampoyigi.cart::default.label_stock_checkout',
//                'type'    => 'switch',
//                'default'  => true,
//                'comment' => 'lang:sampoyigi.cart::default.help_stock_checkout',
//            ],
//            'show_stock_warning' => [
//                'label'   => 'lang:sampoyigi.cart::default.label_show_stock_warning',
//                'type'    => 'switch',
//                'comment' => 'lang:sampoyigi.cart::default.help_show_stock_warning',
//            ],
//            'menu_quantity'    => [
//                'label' => 'lang:sampoyigi.cart::default.label_menu_quantity',
//                'span'  => 'left',
//                'type'  => 'switch',
//            ],
//            'add_comment'      => [
//                'label' => 'lang:sampoyigi.cart::default.label_add_comment',
//                'span'  => 'right',
//                'type'  => 'switch',
//            ],
//            'conditions'      => [
//                'label' => 'lang:sampoyigi.cart::default.label_cart_totals',
//                'type'  => 'partial',
//                'sortable'  => TRUE,
//                'form'  => [
//                    'fields' => [
//                        'priority'       => [
//                            'label' => 'lang:sampoyigi.cart::default.column_priority',
//                            'type' => 'hidden',
//                        ],
//                        'code' => [
//                            'label' => 'lang:sampoyigi.cart::default.column_code',
//                            'type' => 'text',
//                        ],
//                        'label'       => [
//                            'label' => 'lang:sampoyigi.cart::default.column_title',
//                            'type' => 'text',
//                        ],
//                        'status'       => [
//                            'label' => 'lang:sampoyigi.cart::default.column_display',
//                            'type'  => 'switch',
//                            'default'  => true,
//                        ],
//                    ],
//                ],
//            ],
        ],
    ],
];