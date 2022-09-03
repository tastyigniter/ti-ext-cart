<?php

namespace Igniter\Cart\Classes;

use Admin\Models\Menu_item_option_values_model;
use Admin\Models\Menu_item_options_model;
use Igniter\Cart\Models\CartSettings;
use Igniter\Cart\Models\Menus_model;
use Igniter\Coupons\Models\Coupons_model;
use Igniter\Flame\Cart\CartCondition;
use Igniter\Flame\Cart\CartItem;
use Igniter\Flame\Cart\Exceptions\InvalidRowIDException;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Traits\Singleton;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;

class CartManager
{
    use Singleton;

    /**
     * @var \Igniter\Flame\Cart\Cart
     */
    protected $cart;

    /**
     * @var \Igniter\Local\Classes\Location
     */
    protected $location;

    /**
     * @var \Igniter\Cart\Models\CartSettings|\System\Actions\SettingsModel
     */
    protected $settings;

    protected $checkStock = true;

    public function initialize()
    {
        $this->cart = App::make('cart');
        $this->location = App::make('location');
        $this->settings = CartSettings::instance();

        $this->loadCartConditions();
    }

    public function checkStock(bool $checkStock)
    {
        $this->checkStock = $checkStock;

        return $this;
    }

    public function getCart()
    {
        return $this->cart;
    }

    public function getCartItem($rowId)
    {
        try {
            return $this->cart->get($rowId);
        }
        catch (InvalidRowIDException $ex) {
            throw new ApplicationException(lang('igniter.cart::default.alert_no_menu_item_found'));
        }
    }

    public function findMenuItem($menuId)
    {
        if (!is_numeric($menuId))
            throw new ApplicationException(lang('igniter.cart::default.alert_no_menu_selected'));

        if (!$menuItem = Menus_model::findBy($menuId, $this->location->current()))
            throw new ApplicationException(lang('igniter.cart::default.alert_menu_not_found'));

        return $menuItem;
    }

    public function addCartItem($menuId, array $properties = [])
    {
        return $this->addOrUpdateCartItem(array_merge($properties, [
            'menuId' => $menuId,
        ]));
    }

    public function updateCartItem($rowId, array $properties = [])
    {
        return $this->addOrUpdateCartItem(array_merge($properties, [
            'rowId' => $rowId,
        ]));
    }

    public function addOrUpdateCartItem(array $postData)
    {
        $rowId = array_get($postData, 'rowId');
        $menuId = array_get($postData, 'menuId');
        $quantity = array_get($postData, 'quantity');
        $comment = array_get($postData, 'comment');
        $menuOptions = array_get($postData, 'menu_options', []);

        if ($quantity <= 0) {
            $this->removeCartItem($rowId);

            return;
        }

        $this->validateLocation();

        $this->validateOrderTime();

        $cartItem = null;
        $menuItem = $this->findMenuItem($menuId);
        if ($rowId && $cartItem = $this->getCartItem($rowId))
            $menuItem = $cartItem->model;

        $this->validateCartMenuItem($menuItem, $quantity);

        $options = $this->prepareCartMenuItemOptions($menuItem->menu_options, $menuOptions);

        if (is_null($cartItem))
            return $this->cart->add($menuItem, $quantity, $options, $comment);

        return $this->cart->update($cartItem->rowId, [
            'name' => $menuItem->getBuyableName(),
            'price' => $menuItem->getBuyablePrice(),
            'qty' => $quantity,
            'options' => $options,
            'comment' => $comment,
        ]);
    }

    public function removeCartItem($rowId)
    {
        $this->getCartItem($rowId);

        $this->cart->remove($rowId);
    }

    public function updateCartItemQty($rowId, $quantityOrAction = 0)
    {
        $cartItem = $this->getCartItem($rowId);
        $menuItem = $this->findMenuItem($cartItem->id);

        if ($quantityOrAction === 'plus') {
            $quantity = $cartItem->qty + $menuItem->minimum_qty;
        }
        elseif ($quantityOrAction === 'minus') {
            $quantity = max($cartItem->qty - $menuItem->minimum_qty, 0);
        }
        else {
            $quantity = $quantityOrAction > 1 ? $quantityOrAction : $cartItem->qty - $menuItem->minimum_qty;
        }

        return $this->cart->update($rowId, $quantity);
    }

    public function removeCondition($name)
    {
        return $this->cart->removeCondition($name);
    }

    public function applyCondition($name, array $metaData = [])
    {
        if (!$condition = $this->cart->getCondition($name))
            return false;

        $condition->setMetaData($metaData);

        $this->cart->loadCondition($condition);

        return $condition;
    }

    public function applyCouponCondition($code)
    {
        $condition = Event::fire('igniter.cart.beforeApplyCoupon', [$code], true);
        if ($condition instanceof CartCondition)
            return $condition;

        if (strlen($code)) {
            if (!Coupons_model::isEnabled()->whereCodeAndLocation($code, $this->location->getId())->exists())
                throw new ApplicationException(lang('igniter.cart::default.alert_coupon_invalid'));
        }

        return $this->applyCondition('coupon', ['code' => $code]);
    }

    protected function loadCartConditions()
    {
        $conditionManager = CartConditionManager::instance();

        $conditions = $this->settings->get('conditions') ?: [];
        foreach ($conditions as $definition) {
            if (!(bool)array_get($definition, 'status', true))
                continue;

            $definition['cartInstance'] = $this->cart->currentInstance();

            $className = array_get($definition, 'className');
            $condition = $conditionManager->makeCondition($className, $definition);

            $this->cart->loadCondition($condition);
        }
    }

    //
    //
    //

    public function validateCartMenuItem($menuItem, $quantity)
    {
        $this->validateMenuItem($menuItem);

        $this->validateMenuItemMinQty($menuItem, $quantity);

        if ($this->checkStock)
            $this->validateMenuItemStockQty($menuItem, $quantity);

        $this->validateMenuItemLocation($menuItem);
    }

    protected function prepareCartMenuItemOptions(Collection $menuOptions, array $selected)
    {
        $selected = collect($selected)->keyBy('menu_option_id');
        $menuOptions = $menuOptions->keyBy('menu_option_id')->sortBy('priority');

        return $menuOptions->map(function (Menu_item_options_model $menuOption) use ($selected) {
            $selectedOption = $selected->get($menuOption->getKey());
            $selectedValues = array_filter(array_get($selectedOption, 'option_values', []));

            if ($menuOption->option->display_type != 'quantity') {
                $selectedValues = array_filter($selectedValues, 'ctype_digit');
            }

            $this->validateMenuItemOption($menuOption, $selectedValues);

            $menuOptionValues = $this->prepareCartItemOptionValues(
                $menuOption->menu_option_values, $selectedValues
            );

            return $menuOptionValues->isNotEmpty() ? [
                'id' => $menuOption->menu_option_id,
                'name' => $menuOption->option_name,
                'values' => $menuOptionValues->all(),
            ] : false;
        })->filter()->all();
    }

    protected function prepareCartItemOptionValues(Collection $menuOptionValues, array $selectedValues)
    {
        $menuOptionValues = $menuOptionValues->keyBy('menu_option_value_id')->sortBy('priority');

        return $menuOptionValues
            ->map(function (Menu_item_option_values_model $optionValue) use ($selectedValues) {
                $selectedIds = array_column($selectedValues, 'id') ?: $selectedValues;
                if (!in_array($optionValue->menu_option_value_id, $selectedIds))
                    return;

                $selectedValue = collect($selectedValues)->firstWhere('id', $optionValue->menu_option_value_id);

                $qty = (int)array_get($selectedValue, 'qty', 1);
                if ($qty < 1) return;

                return [
                    'id' => $optionValue->menu_option_value_id,
                    'qty' => $qty,
                    'name' => $optionValue->name,
                    'price' => $optionValue->price,
                ];
            })->filter();
    }

    //
    //
    //

    public function validateContents()
    {
        if (!$this->cart->count())
            throw new ApplicationException(lang('igniter.cart::default.checkout.alert_no_menu_to_order'));

        $this->cart->content()->each(function (CartItem $cartItem) {
            $menuItem = $cartItem->model;

            $this->validateCartMenuItem($menuItem, $cartItem->qty);

            $menuOptions = $menuItem->menu_options->keyBy('menu_option_id');

            $cartItem->options->each(function ($cartItemOption) use ($menuOptions) {
                $this->validateMenuItemOption(
                    $menuOptions->get($cartItemOption->id),
                    $cartItemOption->values->toArray()
                );
            });
        });
    }

    public function validateLocation()
    {
        if (!$this->location->current())
            throw new ApplicationException(lang('igniter.local::default.alert_location_required'));

        if ($this->location->orderTypeIsDelivery() && $this->location->requiresUserPosition() && !$this->location->userPosition()->isValid())
            throw new ApplicationException(lang('igniter.cart::default.alert_no_search_query'));
    }

    public function validateOrderTime()
    {
        if (!$this->location->current())
            throw new ApplicationException(lang('igniter.local::default.alert_location_required'));

        if ($this->location->checkNoOrderTypeAvailable())
            throw new ApplicationException(lang('igniter.local::default.alert_order_type_required'));

        $orderType = $this->location->getOrderType();
        if (!$orderType || $orderType->isDisabled())
            throw new ApplicationException(sprintf(lang('igniter.local::default.alert_order_is_unavailable'),
                optional($orderType)->getLabel() ?? $this->location->orderType()
            ));

        if (!$this->location->checkOrderTime())
            throw new ApplicationException(sprintf(lang('igniter.cart::default.checkout.alert_outside_hours'),
                optional($orderType)->getLabel() ?? $this->location->orderType()
            ));
    }

    public function validateMenuItem(Menus_model $menuItem)
    {
        // if menu mealtime is enabled and menu is outside mealtime
        if (!$menuItem->isAvailable($this->location->orderDateTime())) {
            throw new ApplicationException(
                sprintf(
                    lang('igniter.cart::default.alert_menu_not_within_mealtimes'),
                    $menuItem->menu_name,
                    $menuItem->mealtimes->map(function ($mealtime) {
                        return sprintf(
                            lang('igniter.cart::default.alert_menu_not_within_mealtimes_option'),
                            $mealtime->mealtime_name,
                            $mealtime->start_time,
                            $mealtime->end_time
                        );
                    })->join(', ')
                )
            );
        }

        $orderType = $this->location->getOrderType();
        if ($menuItem->hasOrderTypeRestriction($orderType->getCode())) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_menu_order_restriction'),
                $orderType->getLabel()
            ));
        }
    }

    public function validateMenuItemMinQty(Menus_model $menuItem, $quantity)
    {
        if ($quantity == 0 || $menuItem->minimum_qty == 0)
            return;

        // Quantity is valid if its divisive by the minimum quantity
        if (($quantity % $menuItem->minimum_qty) > 0) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_qty_is_invalid'), $menuItem->minimum_qty
            ));
        }

        // if cart quantity is less than minimum quantity
        if (!$menuItem->checkMinQuantity($quantity)) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_qty_is_below_min_qty'), $menuItem->minimum_qty
            ));
        }
    }

    public function validateMenuItemStockQty(Menus_model $menuItem, $quantity)
    {
        // checks if stock quantity is less than to zero
        if ($menuItem->outOfStock($this->location->getId())) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_out_of_stock'), $menuItem->menu_name
            ));
        }

        // checks if stock quantity is less than the cart quantity
        if (!$menuItem->checkStockLevel($quantity, $this->location->getId())) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_low_on_stock'),
                $menuItem->menu_name,
                $menuItem->stocks->where('location_id', $this->location->getId())->value('quantity')
            ));
        }
    }

    public function validateMenuItemOption(Menu_item_options_model $menuOption, $selectedValues)
    {
        if ($menuOption->isRequired() && !$selectedValues) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_option_required'), $menuOption->option_name
            ));
        }

        if ('quantity' == $menuOption->display_type) {
            $countSelected = array_reduce($selectedValues, function ($qty, $selectedValue) {
                return $qty + $selectedValue['qty'];
            });
        }
        else {
            $countSelected = count($selectedValues);
        }

        if ($menuOption->min_selected > 0 || $menuOption->max_selected > 0) {
            if (!($countSelected >= $menuOption->min_selected && $countSelected <= $menuOption->max_selected)) {
                throw new ApplicationException(sprintf(
                    lang('igniter.cart::default.alert_option_selected'),
                    $menuOption->option_name,
                    $menuOption->min_selected,
                    $menuOption->max_selected
                ));
            }
        }
    }

    public function validateMenuItemLocation(Menus_model $menuItem)
    {
        if ($menuItem->locations && $menuItem->locations->isNotEmpty()) {
            if (!$menuItem->locations->keyBy('location_id')->has($this->location->getId())) {
                throw new ApplicationException(sprintf(
                    lang('igniter.cart::default.alert_menu_location_restricted'), $menuItem->menu_name
                ));
            }
        }
    }

    //
    //
    //

    public function cartTotalIsBelowMinimumOrder()
    {
        return !$this->location->checkMinimumOrderTotal($this->cart->subtotal());
    }

    public function deliveryChargeIsUnavailable()
    {
        return $this->location->orderTypeIsDelivery()
            && $this->location->deliveryAmount($this->cart->subtotal()) < 0;
    }
}
