<?php

namespace Igniter\Cart\Classes;

use Admin\Models\Menu_item_option_values_model;
use Admin\Models\Menu_item_options_model;
use Igniter\Cart\Models\Coupons_model;
use Igniter\Cart\Models\Menus_model;
use Igniter\Flame\Cart\CartItem;
use Igniter\Flame\Cart\Exceptions\InvalidRowIDException;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Traits\Singleton;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

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

    protected $checkStock = FALSE;

    public function initialize()
    {
        $this->cart = App::make('cart');
        $this->location = App::make('location');
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
            throw new ApplicationException($ex->getMessage());
        }
    }

    public function findMenuItem($menuId)
    {
        if (!is_numeric($menuId))
            throw new ApplicationException(lang('igniter.cart::default.alert_no_menu_selected'));

        if (!$menuItem = Menus_model::find($menuId))
            throw new ApplicationException(lang('igniter.cart::default.alert_menu_not_found'));

        return $menuItem;
    }

    public function addCartItem($menuId, array $properties = [])
    {
        return $this->addOrUpdateCartItem(array_merge($properties, [
            'menuId' => $menuId,
        ]));
    }

    public function updateCartItem($menuId, array $properties = [])
    {
        return $this->addOrUpdateCartItem(array_merge($properties, [
            'menuId' => $menuId,
        ]));
    }

    public function addOrUpdateCartItem(array $postData)
    {
        $rowId = array_get($postData, 'rowId');
        $menuId = array_get($postData, 'menuId');
        $quantity = array_get($postData, 'quantity');
        $comment = array_get($postData, 'comment');
        $menuOptions = array_get($postData, 'menu_options', []);

        $this->validateLocation();

        $cartItem = null;
        $menuItem = $this->findMenuItem($menuId);
        if ($rowId AND $cartItem = $this->getCartItem($rowId))
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

        return $this->cart->remove($rowId);
    }

    public function updateCartItemQty($rowId, $quantity = 0)
    {
        $cartItem = $this->getCartItem($rowId);
        $menuItem = $this->findMenuItem($cartItem->id);

        $quantity = $quantity > 1 ? $quantity : $cartItem->qty - $menuItem->minimum_qty;

        return $this->cart->update($rowId, $quantity);
    }

    public function removeCondition($name)
    {
        return $this->cart->removeCondition($name);
    }

    public function applyCondition($name, array $metaData = [])
    {
        if (!$condition = $this->cart->getCondition($name))
            return FALSE;

        $condition->setMetaData($metaData);

        $this->cart->loadCondition($condition);

        return $condition;
    }

    public function applyCouponCondition($code)
    {
        if (strlen($code)) {
            $coupon = Coupons_model::isEnabled()->whereCode($code)->first();
            if (!$coupon)
                throw new ApplicationException(lang('igniter.cart::default.alert_coupon_invalid'));
        }

        return $this->applyCondition('coupon', ['code' => $code]);
    }

    //
    //
    //

    protected function validateCartMenuItem($menuItem, $quantity)
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
            $selectedValues = array_filter($selectedValues, 'ctype_digit');

            $this->validateMenuItemOption($menuOption, $selectedValues);

            $menuOptionValues = $this->prepareCartItemOptionValues(
                $menuOption->menu_option_values, $selectedValues
            );

            return $menuOptionValues->isNotEmpty() ? [
                'id' => $menuOption->menu_option_id,
                'name' => $menuOption->option_name,
                'values' => $menuOptionValues->all(),
            ] : FALSE;
        })->filter()->all();
    }

    protected function prepareCartItemOptionValues(Collection $menuOptionValues, array $selectedValues)
    {
        $menuOptionValues = $menuOptionValues->keyBy('menu_option_value_id')->sortBy('priority');

        return $menuOptionValues
            ->map(function (Menu_item_option_values_model $optionValue) use ($selectedValues) {
                if (!in_array($optionValue->menu_option_value_id, $selectedValues))
                    return;

                return [
                    'id' => $optionValue->menu_option_value_id,
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
            throw new ApplicationException(lang('igniter.cart::default.alert_location_required'));

        if (!$this->location->current()->hasFutureOrder() AND $this->location->isClosed())
            throw new ApplicationException(lang('igniter.cart::default.alert_location_closed'));

        if (!$this->location->checkOrderType($orderType = $this->location->orderType()))
            throw new ApplicationException(lang('igniter.local::default.alert_'.$orderType.'_unavailable'));

        if ($this->location->orderTypeIsDelivery() AND $this->location->requiresUserPosition() AND !$this->location->userPosition()->isValid())
            throw new ApplicationException(lang('igniter.cart::default.alert_no_search_query'));
    }

    public function validateOrderTime()
    {
        $orderDateTime = $this->location->orderDateTime();
        if (!$orderDateTime OR !$this->location->checkOrderTime($orderDateTime))
            throw new ApplicationException(lang('igniter.cart::default.checkout.alert_no_delivery_time'));
    }

    public function validateMenuItem(Menus_model $menuItem)
    {
        // if menu mealtime is enabled and menu is outside mealtime
        if (!$menuItem->isAvailable()) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_menu_not_within_mealtime'),
                $menuItem->menu_name,
                $menuItem->mealtime->mealtime_name,
                $menuItem->mealtime->start_time,
                $menuItem->mealtime->end_time
            ));
        }

        if ($menuItem->hasOrderTypeRestriction($orderType = $this->location->orderType())) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_menu_order_restriction'),
                lang('igniter.local::default.text_'.$orderType)
            ));
        }
    }

    public function validateMenuItemMinQty(Menus_model $menuItem, $quantity)
    {
        if ($quantity == 0 OR $menuItem->minimum_qty == 0)
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
        if ($menuItem->outOfStock()) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_out_of_stock'), $menuItem->menu_name
            ));
        }

        // checks if stock quantity is less than the cart quantity
        if (!$menuItem->checkStockLevel($quantity)) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_low_on_stock'),
                $menuItem->menu_name,
                $menuItem->stock_qty
            ));
        }
    }

    public function validateMenuItemOption(Menu_item_options_model $menuOption, $selectedValues)
    {
        if ($menuOption->isRequired() AND !$selectedValues) {
            throw new ApplicationException(sprintf(
                lang('igniter.cart::default.alert_option_required'), $menuOption->option_name
            ));
        }

        $countSelected = count($selectedValues);
        if ($menuOption->min_selected > 0 OR $menuOption->max_selected > 0) {
            if (!($countSelected >= $menuOption->min_selected AND $countSelected <= $menuOption->max_selected)) {
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
        if ($menuItem->locations AND $menuItem->locations->isNotEmpty()) {
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
        return $this->location->orderTypeIsDelivery()
            AND !$this->location->checkMinimumOrder($this->cart->total());
    }
}