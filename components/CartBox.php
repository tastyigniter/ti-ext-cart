<?php

namespace Igniter\Cart\Components;

use Exception;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Models\CartSettings;
use Igniter\Flame\Cart\Facades\Cart;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Facades\Location;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class CartBox extends \System\Classes\BaseComponent
{
    use \Main\Traits\UsesPage;

    /**
     * @var \Igniter\Cart\Classes\CartManager
     */
    protected $cartManager;

    public function initialize()
    {
        $this->cartManager = CartManager::instance()->checkStock(
            (bool)$this->property('checkStockCheckout', TRUE)
        );
    }

    public function defineProperties()
    {
        return [
            'showCartItemThumb' => [
                'label' => 'Show cart menu item image in the popup',
                'type' => 'switch',
                'default' => FALSE,
                'validationRule' => 'required|boolean',
            ],
            'cartItemThumbWidth' => [
                'label' => 'Cart item image width',
                'type' => 'number',
                'span' => 'left',
                'default' => 720,
                'validationRule' => 'integer',
            ],
            'cartItemThumbHeight' => [
                'label' => 'Cart item image height',
                'type' => 'number',
                'span' => 'right',
                'default' => 300,
                'validationRule' => 'integer',
            ],
            'checkStockCheckout' => [
                'label' => 'lang:igniter.cart::default.help_stock_checkout',
                'type' => 'switch',
                'default' => TRUE,
                'validationRule' => 'required|boolean',
            ],
            'pageIsCheckout' => [
                'label' => 'Whether this component is loaded on the checkout page',
                'type' => 'switch',
                'default' => FALSE,
                'validationRule' => 'required|boolean',
            ],
            'pageIsCart' => [
                'label' => 'Whether this component is loaded on the cart page',
                'type' => 'switch',
                'default' => FALSE,
                'validationRule' => 'required|boolean',
            ],
            'hideZeroOptionPrices' => [
                'label' => 'Whether to hide zero prices on options',
                'type' => 'switch',
                'default' => FALSE,
                'validationRule' => 'required|boolean',
            ],
            'checkoutPage' => [
                'label' => 'Checkout Page',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'checkout'.DIRECTORY_SEPARATOR.'checkout',
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'localBoxAlias' => [
                'label' => 'Specify the LocalBox component alias used to refresh the localbox after the order type is changed',
                'type' => 'text',
                'default' => 'localBox',
                'validationRule' => 'required|regex:/^[a-z0-9\-_]+$/i',
            ],
        ];
    }

    public function onRun()
    {
        $this->addJs('js/cartbox.js', 'cart-box-js');
        $this->addJs('js/cartitem.js', 'cart-item-js');
        $this->addJs('js/cartbox.modal.js', 'cart-box-modal-js');

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['showCartItemThumb'] = $this->property('showCartItemThumb', FALSE);
        $this->page['cartItemThumbWidth'] = $this->property('cartItemThumbWidth');
        $this->page['cartItemThumbHeight'] = $this->property('cartItemThumbHeight');
        $this->page['pageIsCart'] = $this->property('pageIsCart');
        $this->page['pageIsCheckout'] = $this->property('pageIsCheckout');
        $this->page['hideZeroOptionPrices'] = (bool)$this->property('hideZeroOptionPrices');

        $this->page['checkoutEventHandler'] = $this->getEventHandler('onProceedToCheckout');
        $this->page['updateCartItemEventHandler'] = $this->getEventHandler('onUpdateCart');
        $this->page['applyCouponEventHandler'] = $this->getEventHandler('onApplyCoupon');
        $this->page['applyTipEventHandler'] = $this->getEventHandler('onApplyTip');
        $this->page['loadCartItemEventHandler'] = $this->getEventHandler('onLoadItemPopup');
        $this->page['removeCartItemEventHandler'] = $this->getEventHandler('onRemoveItem');
        $this->page['removeConditionEventHandler'] = $this->getEventHandler('onRemoveCondition');
        $this->page['refreshCartEventHandler'] = $this->getEventHandler('onRefresh');

        $this->page['cart'] = $this->cartManager->getCart();
    }

    public function fetchPartials()
    {
        $this->prepareVars();

        return [
            '#cart-items' => $this->renderPartial('@items'),
            '#cart-coupon' => $this->renderPartial('@coupon_form'),
            '#cart-tip' => $this->renderPartial('@tip_form'),
            '#cart-totals' => $this->renderPartial('@totals'),
            '#cart-buttons' => $this->renderPartial('@buttons'),
            '[data-cart-total]' => currency_format(Cart::total()),
            '#notification' => $this->renderPartial('flash'),
        ];
    }

    public function onRefresh()
    {
        return $this->fetchPartials();
    }

    public function onLoadItemPopup()
    {
        $menuItem = $this->cartManager->findMenuItem(post('menuId'));

        $cartItem = null;
        if (strlen($rowId = post('rowId'))) {
            $cartItem = $this->cartManager->getCartItem($rowId);
            $menuItem = $cartItem->model;
        }

        $this->cartManager->validateLocation();

        $this->cartManager->validateOrderTime();

        $this->cartManager->validateMenuItem($menuItem);

        $this->cartManager->validateMenuItemStockQty($menuItem, $cartItem ? $cartItem->qty : 0);

        $this->controller->pageCycle();

        return $this->renderPartial('@item_modal', [
            'formHandler' => $this->getEventHandler('onUpdateCart'),
            'cartItem' => $cartItem,
            'menuItem' => $menuItem,
        ]);
    }

    public function onUpdateCart()
    {
        try {
            $postData = post();

            $this->cartManager->addOrUpdateCartItem($postData);

            $this->controller->pageCycle();

            return $this->fetchPartials();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function onRemoveItem()
    {
        try {
            $rowId = (string)post('rowId');
            $quantity = (int)post('quantity');

            $this->cartManager->updateCartItemQty($rowId, $quantity);

            $this->controller->pageCycle();

            return $this->fetchPartials();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function onApplyCoupon()
    {
        try {
            $this->cartManager->applyCouponCondition(post('code'));

            $this->controller->pageCycle();

            return $this->fetchPartials();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function onApplyTip()
    {
        try {
            $amountType = post('amount_type');
            if (!in_array($amountType, ['none', 'amount', 'custom']))
                throw new ApplicationException(lang('igniter.cart::default.alert_tip_not_applied'));

            $amount = post('amount');
            if (preg_match('/^\d+([\.\d]{2})?([%])?$/', $amount) === FALSE)
                throw new ApplicationException(lang('igniter.cart::default.alert_tip_not_applied'));

            $this->cartManager->applyCondition('tip', [
                'amountType' => $amountType,
                'amount' => $amount,
            ]);

            $this->controller->pageCycle();

            return $this->fetchPartials();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function onRemoveCondition()
    {
        try {
            if (!strlen($conditionId = post('conditionId')))
                return;

            $this->cartManager->removeCondition($conditionId);
            $this->controller->pageCycle();

            return $this->fetchPartials();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function onProceedToCheckout()
    {
        try {
            if (!is_numeric($id = post('locationId')) || !($location = Location::getById($id)) || !$location->location_status)
                throw new ApplicationException(lang('igniter.local::default.alert_location_required'));

            Location::setCurrent($location);

            $redirectUrl = $this->controller->pageUrl($this->property('checkoutPage'));

            return Redirect::to($redirectUrl);
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function locationIsClosed()
    {
        return !Location::instance()->checkOrderTime();
    }

    public function hasMinimumOrder()
    {
        return $this->cartManager->cartTotalIsBelowMinimumOrder()
            || $this->cartManager->deliveryChargeIsUnavailable();
    }

    public function buttonLabel($checkoutComponent = null)
    {
        if ($this->locationIsClosed())
            return lang('igniter.cart::default.text_is_closed');

        if (!$this->property('pageIsCheckout'))
            return lang('igniter.cart::default.button_order');

        if ($checkoutComponent && !$checkoutComponent->canConfirmCheckout())
            return lang('igniter.cart::default.button_payment');

        return lang('igniter.cart::default.button_confirm');
    }

    public function getLocationId()
    {
        return Location::instance()->getId();
    }

    public function tippingEnabled()
    {
        return (bool)CartSettings::get('enable_tipping');
    }

    public function tippingAmounts()
    {
        $result = [];

        $tipValueType = CartSettings::get('tip_value_type', 'F');
        $amounts = (array)CartSettings::get('tip_amounts', []);

        $amounts = sort_array($amounts, 'priority');

        foreach ($amounts as $index => $amount) {
            $amount['valueType'] = $tipValueType;
            $result[$index] = (object)$amount;
        }

        return $result;
    }

    public function tippingSelectedAmount()
    {
        return optional($this->cartManager->getCart()->getCondition('tip'))->getMetaData('amount', 0) ?? 0;
    }

    public function tippingSelectedType()
    {
        return optional($this->cartManager->getCart()->getCondition('tip'))->getMetaData('amountType', 'none') ?? 'none';
    }

    public function getOptionQuantityTypeValue($cartItem, $optionValue)
    {
        $value = 0;
        $menuOptionValueId = $optionValue->menu_option_value_id;
        if ($cartItem && $cartItem->hasOptionValue($menuOptionValueId)) {
            $cartItem->options->search(function ($option) use ($menuOptionValueId, &$value) {
                $option->values->each(function ($opt) use ($menuOptionValueId, &$value) {
                    if ($opt->id == $menuOptionValueId) {
                        $value = $opt->qty;
                    }
                });
            });
        }

        return $value;
    }
}
