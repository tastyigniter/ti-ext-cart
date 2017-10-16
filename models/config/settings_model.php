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
            'menu_quantity'    => [
                'label' => 'lang:label_menu_quantity',
                'span'  => 'left',
                'type'  => 'switch',
            ],
            'add_comment'      => [
                'label' => 'lang:label_add_comment',
                'span'  => 'right',
                'type'  => 'switch',
            ],
            'show_cart_images' => [
                'label' => 'lang:label_show_cart_images',
                'type'  => 'switch',
            ],
            'cart_images_h'    => [
                'label' => 'lang:label_cart_images_h',
                'span'  => 'left',
                'type'  => 'number',
            ],
            'cart_images_w'    => [
                'label' => 'lang:label_cart_images_w',
                'span'  => 'right',
                'type'  => 'number',
            ],
            'cart_totals'      => [
                'label' => 'lang:label_cart_image_size',
                'span'  => 'right',
                'type'  => 'repeater',
                'form'  => [
                    'fields' => [

                    ],
                ],
            ],
        ],
    ],
];