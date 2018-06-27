<?php namespace SamPoyigi\Cart\Components;

use ApplicationException;
use Cart;
use Exception;
use Location;
use Main\Template\Page;
use Redirect;
use Request;
use SamPoyigi\Cart\Models\CartSettings;
use SamPoyigi\Cart\Models\Coupons_model;
use SamPoyigi\Cart\Models\Menus_model;

class CartBox extends \System\Classes\BaseComponent
{
    public function defineProperties()
    {
        return [
            'timeFormat'         => [
                'label'   => 'Time format',
                'type'    => 'text',
                'default' => 'D H:i a',
            ],
            'checkStockCheckout' => [
                'label'   => 'lang:sampoyigi.cart::default.help_stock_checkout',
                'type'    => 'switch',
                'default' => FALSE,
            ],
            'pageIsCheckout'     => [
                'label'   => 'Whether this component is loaded on the checkout page',
                'type'    => 'switch',
                'default' => FALSE,
            ],
            'pageIsCart'         => [
                'label'   => 'Whether this component is loaded on the cart page',
                'type'    => 'switch',
                'default' => FALSE,
            ],
            'checkoutPage'       => [
                'label'   => 'Checkout Page',
                'type'    => 'select',
                'default' => 'checkout/checkout',
            ],
        ];
    }

    public static function getCheckoutPageOptions()
    {
        return Page::lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->addCss('css/cartbox.css', 'cart-box-css');
        $this->addJs('js/cartbox.js', 'cart-box-js');
        $this->addJs('js/cartitem.js', 'cart-item-js');
        $this->addJs('js/cartbox.modal.js', 'cart-box-modal-js');

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['cartBoxTimeFormat'] = $this->property('timeFormat');
        $this->page['pageIsCart'] = $this->property('pageIsCart');
        $this->page['pageIsCheckout'] = $this->property('pageIsCheckout');

        $this->page['checkoutEventHandler'] = $this->getEventHandler('onProceedToCheckout');
        $this->page['changeOrderTypeEventHandler'] = $this->getEventHandler('onChangeOrderType');
        $this->page['updateCartItemEventHandler'] = $this->getEventHandler('onUpdateCart');
        $this->page['applyCouponEventHandler'] = $this->getEventHandler('onApplyCoupon');
        $this->page['loadCartItemEventHandler'] = $this->getEventHandler('onLoadItemPopup');
        $this->page['removeCartItemEventHandler'] = $this->getEventHandler('onRemoveItem');
        $this->page['removeConditionEventHandler'] = $this->getEventHandler('onRemoveCondition');

        $this->prepareVarsFromCart();

        $this->prepareVarsFromLocation();
    }

    protected function prepareVarsFromCart()
    {
        $this->loadAvailableConditions();
        $this->page['cartItemsCount'] = Cart::count();
        $this->page['cartTotal'] = Cart::total();
        $this->page['cartSubtotal'] = Cart::subtotal();
        $this->page['cartContent'] = Cart::content();
        $this->page['cartConditions'] = Cart::conditions();
    }

    protected function prepareVarsFromLocation()
    {
        $this->page['isClosed'] = Location::isClosed();
        $this->page['orderType'] = Location::orderType();
        $this->page['canAcceptOrder'] = Location::checkOrderType();
        $this->page['minOrderTotal'] = Location::minimumOrder(Cart::subtotal());
        $this->page['cartTotalIsAboveMinTotal'] = Location::checkMinimumOrder(Cart::subtotal());

        $this->page['hasDelivery'] = Location::current()->hasDelivery();
        $this->page['hasCollection'] = Location::current()->hasCollection();
        $this->page['deliveryMinutes'] = Location::current()->deliveryMinutes();
        $this->page['collectionMinutes'] = Location::current()->collectionMinutes();
        $this->page['deliverySchedule'] = Location::deliverySchedule();
        $this->page['collectionSchedule'] = Location::collectionSchedule();
    }

    public function onChangeOrderType()
    {
        try {
            if (!$location = Location::current())
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_location_required'));

            if (!Location::checkOrderType($orderType = post('type')))
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_'.$orderType.'_unavailable'));

            Location::updateOrderType($orderType);

            $this->pageCycle();

            $partials = [
                '#notification' => $this->renderPartial('flash'),
                '#cart-control' => $this->renderPartial('@control'),
                '#cart-totals'  => $this->renderPartial('@totals'),
                '#cart-buttons' => $this->renderPartial('@buttons'),
            ];

            if ($this->property('pageIsCheckout'))
                return Redirect::to($this->pageUrl($this->property('checkoutPage')));

            return $partials;
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage())->now();
        }
    }

    public function onLoadItemPopup()
    {
        if (!is_numeric($menuId = post('menuId')))
            throw new ApplicationException(lang('sampoyigi.cart::default.alert_no_menu_selected'));

        if (!$menuItem = Menus_model::find($menuId))
            throw new ApplicationException(lang('sampoyigi.cart::default.alert_menu_not_found'));

        $cartItem = null;
        if ($rowId = post('rowId')) {
            $cartItem = Cart::get($rowId);
            $menuItem = $cartItem->model;
        }

        return $this->renderPartial('@item_modal', [
            'formHandler' => $this->getEventHandler('onUpdateCart'),
            'cartItem'    => $cartItem,
            'menuItem'    => $menuItem,
        ]);
    }

    public function onUpdateCart()
    {
        try {
            if (!$location = Location::current())
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_location_required'));

            if (Location::isClosed())
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_location_closed'));

            if (!Location::checkOrderType($orderType = Location::orderType()))
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_'.$orderType.'_unavailable'));

            if ($orderType == 'delivery' AND Location::requiresUserPosition() AND !Location::userPosition()->isValid())
                throw new ApplicationException(lang('sampoyigi.local::default.alert_no_search_query'));

            if (!is_numeric($menuId = post('menuId')))
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_no_menu_selected'));

            $menuModel = Menus_model::find($menuId);

            $cartItem = null;
            if ($rowId = post('rowId')) {
                $cartItem = Cart::get($rowId);
                $menuModel = $cartItem->model;
            }

            $quantity = post('quantity');
            $this->validateMenuItem($menuModel, $quantity);

            $options = $this->createCartItemOptionsArray($menuModel, post('menu_options'));

            if ($cartItem) {
                $cartItem = Cart::update($cartItem->rowId, [
                    'name'    => $menuModel->getBuyableName($options),
                    'price'   => $menuModel->getBuyablePrice($options),
                    'qty'     => $quantity,
                    'options' => $options,
                ]);
            }
            else {
                $cartItem = Cart::add($menuModel, $quantity, $options);
            }

            if (strlen($comment = post('comment')))
                $cartItem->setComment($comment);

            $this->pageCycle();

            return [
                '#notification' => $this->renderPartial('flash'),
                '#cart-items'   => $this->renderPartial('@items'),
                '#cart-coupon'  => $this->renderPartial('@coupon_form'),
                '#cart-totals'  => $this->renderPartial('@totals'),
                '#cart-total'   => currency_format(Cart::total()),
                '#cart-buttons' => $this->renderPartial('@buttons'),
            ];
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function onRemoveItem()
    {
        $cartItem = Cart::get($rowId = post('rowId'));

        if (!$menuItem = Menus_model::find($cartItem->id))
            throw new ApplicationException(lang('sampoyigi.cart::default.alert_menu_not_found'));

        $quantity = $cartItem->qty - $menuItem->minimum_qty;
        Cart::update($rowId, post('quantity', $quantity));

        $this->pageCycle();

        return [
            '#notification' => $this->renderPartial('flash'),
            '#cart-items'   => $this->renderPartial('@items'),
            '#cart-coupon'  => $this->renderPartial('@coupon_form'),
            '#cart-totals'  => $this->renderPartial('@totals'),
            '#cart-buttons' => $this->renderPartial('@buttons'),
        ];
    }

    public function onApplyCoupon()
    {
        try {
            $coupon = Coupons_model::isEnabled()->whereCode($code = post('code'))->first();

            if (!$coupon)
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_coupon_invalid'));

            $condition = Cart::getCondition('coupon');

            $condition->setMetaData('code', $code);

            Cart::loadCondition('coupon', $condition);

            $this->pageCycle();

            return [
                '#notification' => $this->renderPartial('flash'),
                '#cart-totals'  => $this->renderPartial('@totals'),
                '#cart-buttons' => $this->renderPartial('@buttons'),
            ];
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function onRemoveCondition()
    {
        $condition = Cart::getCondition($modifierId = post('conditionId'));

        Cart::removeCondition($condition->name);

        $this->pageCycle();

        return [
            '#notification' => $this->renderPartial('flash'),
            '#cart-totals'  => $this->renderPartial('@totals'),
            '#cart-buttons' => $this->renderPartial('@buttons'),
        ];
    }

    public function onProceedToCheckout()
    {
        try {
            if (!is_numeric($id = post('locationId')) OR !$location = Location::getById($id))
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_location_required'));

            Location::setCurrent($location);

            $redirectUrl = $this->pageUrl($this->property('checkoutPage'));

            return Redirect::to($redirectUrl);
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    protected function createCartItemOptionsArray($menuModel, $options)
    {
        $selectedOptions = collect($options)->keyBy('menu_option_id');

        $optionsArray = $menuModel->menu_options->keyBy('menu_option_id')->map(
            function ($menuOption) use ($selectedOptions) {
                $menuOptionId = $menuOption->getKey();
                $selectedOption = $selectedOptions->get($menuOptionId);

                if ($menuOption->isRequired() AND !isset($selectedOption['option_values']))
                    throw new ApplicationException(sprintf(lang('sampoyigi.cart::default.alert_option_required'),
                        $menuOption->option_name));

                if (!isset($selectedOption['option_values']) OR !count($selectedOption['option_values']))
                    return FALSE;

                $option['menu_option_id'] = $menuOption->menu_option_id;
                $option['name'] = $menuOption->option_name;

                $valuesArray = $menuOption->menu_option_values->keyBy('menu_option_value_id')->map(
                    function ($optionValue) use ($selectedOption) {
                        if (!in_array($optionValue->menu_option_value_id, $selectedOption['option_values']))
                            return FALSE;

                        return array_only($optionValue->toArray(), [
                            'menu_option_value_id',
                            'name',
                            'price',
                        ]);
                    }
                );

                $option['price'] = $valuesArray->sum('price');
                $option['values'] = $valuesArray->filter()->all();

                return $option;
            }
        );

        return $optionsArray->filter()->toArray();
    }

    protected function validateMenuItem($menuModel, $quantity)
    {
        if (!$menuModel)
            throw new ApplicationException(lang('sampoyigi.cart::default.alert_menu_not_found'));

        // if menu mealtime is enabled and menu is outside mealtime
        if (!$menuModel->isAvailable())
            throw new ApplicationException(sprintf(lang('sampoyigi.cart::default.alert_menu_not_within_mealtime'),
                $menuModel->menu_name,
                $menuModel->mealtime->mealtime_name,
                $menuModel->mealtime->start_time,
                $menuModel->mealtime->end_time));

        if ($quantity == 0)
            return;

        // Quantity is valid if its divisive by the minimum quantity
        if (($quantity % $menuModel->minimum_qty) > 0)
            throw new ApplicationException(sprintf(lang('sampoyigi.cart::default.alert_qty_is_invalid'),
                $menuModel->minimum_qty));

        // checks if stock quantity is less than or equal to zero
        if ($menuModel->outOfStock())
            throw new ApplicationException(sprintf(lang('sampoyigi.cart::default.alert_out_of_stock'),
                $menuModel->menu_name));

        $checkStock = $this->property('checkStockCheckout', 1);

        // checks if stock quantity is less than the cart quantity
        if ($checkStock AND !$menuModel->checkStockLevel($quantity))
            throw new ApplicationException(sprintf(lang('sampoyigi.cart::default.alert_low_on_stock'),
                $menuModel->menu_name,
                $menuModel->stock_qty));

        // if cart quantity is less than minimum quantity
        if (!$menuModel->checkMinQuantity($quantity))
            throw new ApplicationException(sprintf(lang('sampoyigi.cart::default.alert_qty_is_below_min_qty'),
                $menuModel->minimum_qty));
    }

    protected function loadAvailableConditions()
    {
        $conditions = CartSettings::get('conditions');

        foreach ($conditions as $name => $config) {
            if (!array_get($config, 'status', FALSE))
                continue;

            Cart::loadCondition($name, $config);
        }
    }
}
