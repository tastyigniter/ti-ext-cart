<?php

namespace Igniter\Cart\Tests\Models;

use Igniter\Cart\Models\CartSettings;
use Igniter\System\Actions\SettingsModel;

it('returns true when tipping is enabled', function() {
    CartSettings::clearInternalCache();

    $result = CartSettings::tippingEnabled();

    expect($result)->toBeFalse();
});

it('returns sorted tipping amounts with value type', function() {
    CartSettings::set('tip_amounts', [
        ['priority' => 2, 'amount' => 5],
        ['priority' => 1, 'amount' => 10],
    ]);

    $result = CartSettings::tippingAmounts();

    expect($result)->toHaveCount(2);
});

it('configures cart settings model correctly', function() {
    $cartSettings = new CartSettings;

    expect($cartSettings->implement)->toContain(SettingsModel::class)
        ->and($cartSettings->settingsCode)->toEqual('igniter_cart_settings')
        ->and($cartSettings->settingsFieldsConfig)->toEqual('cartsettings');
});
