<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Order menu option values and order carts were stored as PHP serialized cart objects and read
 * with unserialize(). Option values were also serialized twice before being stored. Convert both
 * columns to JSON, encoded once, so they are read with json_decode() and can never instantiate PHP objects.
 *
 * Legacy values are unserialized without allowing any classes. The resulting incomplete objects are
 * converted to arrays of their properties, so no cart class is instantiated during the conversion.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->convertColumn('order_menus', 'order_menu_id', 'option_values', $this->legacyToJson(...));
        $this->convertColumn('orders', 'order_id', 'cart', $this->legacyToJson(...));
    }

    protected function convertColumn(string $table, string $keyName, string $column, Closure $converter): void
    {
        DB::table($table)
            ->select([$keyName, $column])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderBy($keyName)
            ->chunkById(500, function($records) use ($table, $keyName, $column, $converter): void {
                foreach ($records as $record) {
                    $value = $converter($record->{$column});
                    if ($value !== $record->{$column}) {
                        DB::table($table)->where($keyName, $record->{$keyName})->update([$column => $value]);
                    }
                }
            }, $keyName);
    }

    protected function legacyToJson(string $value): ?string
    {
        if (json_validate($value)) {
            return $value;
        }

        // Option values were serialized twice, so unwrap until the value is no longer a serialized string.
        $decoded = $value;
        for ($depth = 0; $depth < 3 && is_string($decoded); $depth++) {
            $unserialized = @unserialize($decoded, ['allowed_classes' => false]);
            if ($unserialized === false && $decoded !== 'b:0;') {
                break;
            }

            $decoded = $unserialized;
        }

        if ($decoded === $value) {
            return null;
        }

        return json_encode($this->normalize($decoded), JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    protected function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map($this->normalize(...), $value);
        }

        if (!is_object($value)) {
            return $value;
        }

        $className = null;
        $properties = [];
        foreach ((array)$value as $name => $property) {
            // Protected and private property names are prefixed with "\0*\0" or "\0ClassName\0"
            $name = (string)$name;
            if ($name !== '' && $name[0] === "\0") {
                $name = substr($name, strrpos($name, "\0") + 1);
            }

            if ($name === '__PHP_Incomplete_Class_Name') {
                $className = $property;
            } else {
                $properties[$name] = $property;
            }
        }

        // Collections, such as CartItemOptions and CartContent, are stored as their items
        if (array_key_exists('items', $properties) && (
            array_key_exists('escapeWhenCastingToString', $properties)
            || (is_string($className) && is_a($className, Collection::class, true))
        )) {
            return $this->normalize((array)$properties['items']);
        }

        return $this->normalize($properties);
    }
};
