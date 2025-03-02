<?php

$config['form'] = [
    'fields' => [
        'option_value_id' => [
            'type' => 'hidden',
        ],
        'option_id' => [
            'label' => 'lang:igniter.cart::default.menu_options.label_option_id',
            'type' => 'hidden',
        ],
        'name' => [
            'label' => 'lang:igniter.cart::default.menu_options.label_option_name',
            'type' => 'text',
        ],
        'price' => [
            'label' => 'lang:igniter.cart::default.menu_options.label_option_price',
            'type' => 'currency',
        ],
        'stock_qty' => [
            'label' => 'lang:igniter.cart::default.menus.label_stock_qty',
            'type' => 'stockeditor',
            'span' => 'right',
        ],
        'ingredients' => [
            'label' => 'lang:igniter.cart::default.menus.label_ingredients',
            'type' => 'relation',
            'span' => 'right',
            'attributes' => [
                'data-number-displayed' => 1,
            ],
        ],
        'priority' => [
            'label' => 'lang:igniter.cart::default.menu_options.label_priority',
            'type' => 'hidden',
        ],
    ],
    'rules' => [
        ['option_id', 'lang:igniter.cart::default.menu_options.label_option_id', 'required|integer'],
        ['value', 'lang:igniter.cart::default.menu_options.label_option_value', 'required|min:2|max:255'],
        ['price', 'lang:igniter.cart::default.menu_options.label_option_price', 'required|numeric|min:0'],
        ['priority', 'lang:igniter.cart::default.menu_options.label_option_price', 'integer'],
        ['ingredients.*', 'lang:igniter.cart::default.menus.label_ingredients', 'integer'],
    ],
];

return $config;
