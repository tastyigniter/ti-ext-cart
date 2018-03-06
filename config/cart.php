<?php

/*
|--------------------------------------------------------------------------
| Shoppingcart database settings
|--------------------------------------------------------------------------
|
| Here you can set the connection that the shoppingcart should use when
| storing and restoring a cart.
|
*/
$config['database'] = [
    'connection' => null,
    'table'      => 'cart',
];

/*
|--------------------------------------------------------------------------
| Destroy the cart on user logout
|--------------------------------------------------------------------------
|
| When this option is set to 'true' the cart will automatically
| destroy all cart instances when the user logs out.
|
*/
$config['destroy_on_logout'] = FALSE;

/*
|--------------------------------------------------------------------------
| Default number format
|--------------------------------------------------------------------------
|
| This defaults will be used for the formated numbers if you don't
| set them in the method call.
|
*/
$config['format'] = [
    'decimals'           => 2,
    'decimal_point'      => '.',
    'thousand_seperator' => ',',
];

return $config;