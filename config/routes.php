<?php

Route::any('cart', 'cart/cart', [], function () {
    Route::any('(.+)', 'cart/cart/$1');
});

$route = Route::map();
