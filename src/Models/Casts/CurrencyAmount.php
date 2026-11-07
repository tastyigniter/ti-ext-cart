<?php

declare(strict_types=1);

namespace Igniter\Cart\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Casts a money amount that may be assigned in the default currency's
 * localized format ("0,70", "1.234,50" on a comma-decimal install).
 *
 * The `currency` validation rule accepts that format, but it only normalizes
 * the value inside the validator's data. Whatever is assigned to the model is
 * what gets stored, and a plain `float` cast only applies on read, so the raw
 * "0,70" would reach the database and be truncated to 0.00. This cast
 * normalizes on write, reusing the `currency` rule so both agree on what a
 * valid localized amount is.
 *
 * @implements CastsAttributes<float|string|null, mixed>
 */
class CurrencyAmount implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): float|string|null
    {
        if ($value === null) {
            return null;
        }

        // A value that is still not numeric was never normalized, return it
        // unchanged so model validation can reject it instead of seeing 0.0.
        return is_numeric($value) ? (float)$value : $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === '') {
            return null;
        }

        if (!is_string($value) || is_numeric($value)) {
            return $value;
        }

        $validator = Validator::make(['amount' => $value], ['amount' => ['currency']]);

        return $validator->passes() ? $validator->validated()['amount'] : $value;
    }
}
