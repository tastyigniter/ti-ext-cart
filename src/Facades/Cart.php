<?php

namespace Igniter\Cart\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Igniter\Cart\Cart instance(string|null $instance = null)
 * @method static string currentInstance()
 * @method static array|\Igniter\Cart\CartItem add(void $buyable, int $qty = 0, array $options = [], void $comment = null)
 * @method static \Igniter\Cart\CartItem|bool update(string $rowId, mixed $qty)
 * @method static void remove(string $rowId)
 * @method static \Igniter\Cart\CartItem get(string $rowId)
 * @method static void destroy(mixed $identifier = null)
 * @method static \Igniter\Cart\CartContent content()
 * @method static int count()
 * @method static string total()
 * @method static float subtotal()
 * @method static \Igniter\Cart\CartContent search(\Closure $search)
 * @method static void associate(string $rowId, mixed $model)
 * @method static \Igniter\Cart\CartConditions conditions()
 * @method static void conditionsWithoutApplied()
 * @method static \Igniter\Cart\CartCondition getCondition(void $name)
 * @method static bool removeCondition(void $name)
 * @method static void clearConditions()
 * @method static void condition(void $condition)
 * @method static void loadConditions()
 * @method static void loadCondition(\Igniter\Cart\CartCondition $condition)
 * @method static void clearContent()
 * @method static void store(mixed $identifier)
 * @method static void restore(mixed $identifier)
 * @method static void deleteStored(void $identifier)
 * @method static void keepSession(\Closure $callback)
 *
 * @see \Igniter\Cart\Cart
 */
class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
