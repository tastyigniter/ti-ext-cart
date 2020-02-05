<?php namespace Igniter\Cart\Components;

use ApplicationException;
use Cart;
use Exception;
use Igniter\Cart\Classes\CartManager;
use Location;
use Main\Template\Page;
use Redirect;
use Request;

class CartBox extends \System\Classes\BaseComponent
{
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
            'cartBoxTimeFormat' => [
                'label' => 'Time format for the delivery and pickup time',
                'type' => 'text',
                'default' => 'ddd hh:mm a',
            ],
            'showCartItemThumb' => [
                'label' => 'Show cart menu item image in the popup',
                'type' => 'switch',
                'default' => FALSE,
            ],
            'cartItemThumbWidth' => [
                'label' => 'Cart item image width',
                'type' => 'number',
                'span' => 'left',
                'default' => 720,
            ],
            'cartItemThumbHeight' => [
                'label' => 'Cart item image height',
                'type' => 'number',
                'span' => 'right',
                'default' => 300,
            ],
            'checkStockCheckout' => [
                'label' => 'lang:igniter.cart::default.help_stock_checkout',
                'type' => 'switch',
                'default' => TRUE,
            ],
            'pageIsCheckout' => [
                'label' => 'Whether this component is loaded on the checkout page',
                'type' => 'switch',
                'default' => FALSE,
            ],
            'pageIsCart' => [
                'label' => 'Whether this component is loaded on the cart page',
                'type' => 'switch',
                'default' => FALSE,
            ],
            'checkoutPage' => [
                'label' => 'Checkout Page',
                'type' => 'select',
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
        $this->page['cartBoxTimeFormat'] = $this->property('cartBoxTimeFormat');
        $this->page['pageIsCart'] = $this->property('pageIsCart');
        $this->page['pageIsCheckout'] = $this->property('pageIsCheckout');

        $this->page['checkoutEventHandler'] = $this->getEventHandler('onProceedToCheckout');
        $this->page['changeOrderTypeEventHandler'] = $this->getEventHandler('onChangeOrderType');
        $this->page['updateCartItemEventHandler'] = $this->getEventHandler('onUpdateCart');
        $this->page['applyCouponEventHandler'] = $this->getEventHandler('onApplyCoupon');
        $this->page['loadCartItemEventHandler'] = $this->getEventHandler('onLoadItemPopup');
        $this->page['removeCartItemEventHandler'] = $this->getEventHandler('onRemoveItem');
        $this->page['removeConditionEventHandler'] = $this->getEventHandler('onRemoveCondition');

        $this->page['cart'] = $this->cartManager->getCart();
        $this->page['location'] = Location::instance();
        $this->page['locationCurrent'] = Location::current();
    }

    public function onChangeOrderType()
    {
        try {
            if (!$location = Location::current())
                throw new ApplicationException(lang('igniter.cart::default.alert_location_required'));

            if (!Location::checkOrderType($orderType = post('type')))
                throw new ApplicationException(lang('igniter.cart::default.alert_'.$orderType.'_unavailable'));

            Location::updateOrderType($orderType);

            $this->controller->pageCycle();

            if ($this->property('pageIsCheckout'))
                return Redirect::to($this->controller->pageUrl($this->property('checkoutPage')));

            return [
                '#notification' => $this->renderPartial('flash'),
                '#cart-control' => $this->renderPartial('@control'),
                '#cart-totals' => $this->renderPartial('@totals'),
                '#cart-buttons' => $this->renderPartial('@buttons'),
            ];
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage())->now();
        }
    }

    public function onLoadItemPopup()
    {
        $menuItem = $this->cartManager->findMenuItem(post('menuId'));

        $cartItem = null;
        if (strlen($rowId = post('rowId'))) {
            $cartItem = $this->cartManager->getCartItem($rowId);
            $menuItem = $cartItem->model;
        }

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

            return [
                '#notification' => $this->renderPartial('flash'),
                '#cart-items' => $this->renderPartial('@items'),
                '#cart-coupon' => $this->renderPartial('@coupon_form'),
                '#cart-total' => currency_format(Cart::total()),
                '#cart-totals' => $this->renderPartial('@totals'),
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
        try {
            $rowId = (string)post('rowId');
            $quantity = (int)post('quantity');

            $this->cartManager->updateCartItemQty($rowId, $quantity);

            $this->controller->pageCycle();

            return [
                '#notification' => $this->renderPartial('flash'),
                '#cart-items' => $this->renderPartial('@items'),
                '#cart-coupon' => $this->renderPartial('@coupon_form'),
                '#cart-totals' => $this->renderPartial('@totals'),
                '#cart-buttons' => $this->renderPartial('@buttons'),
            ];
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

            return [
                '#cart-totals' => $this->renderPartial('@totals'),
                '#cart-buttons' => $this->renderPartial('@buttons'),
                '#notification' => $this->renderPartial('flash'),
            ];
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

            return [
                '#notification' => $this->renderPartial('flash'),
                '#cart-totals' => $this->renderPartial('@totals'),
                '#cart-buttons' => $this->renderPartial('@buttons'),
            ];
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }

    public function onProceedToCheckout()
    {
        try {
            if (!is_numeric($id = post('locationId')) OR !$location = Location::getById($id))
                throw new ApplicationException(lang('igniter.cart::default.alert_location_required'));

            Location::setCurrent($location);

            $redirectUrl = $this->controller->pageUrl($this->property('checkoutPage'));

            return Redirect::to($redirectUrl);
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage());
        }
    }
}
