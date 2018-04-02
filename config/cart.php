<?php

/*
|--------------------------------------------------------------------------
| Cart database settings
|--------------------------------------------------------------------------
|
| Here you can set the connection that the cart should use when
| storing and restoring a cart.
|
*/
$config['database'] = [
    'connection' => null,
    'table'      => 'sampoyigi_cart_cart',
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
$config['destroyOnLogout'] = FALSE;

return $config;