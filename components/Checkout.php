<?php

namespace SamPoyigi\Cart\Components;

use Admin\Models\Addresses_model;
use Admin\Models\Payments_model;
use Admin\Traits\ValidatesForm;
use ApplicationException;
use Auth;
use Cart;
use Exception;
use Illuminate\Http\RedirectResponse;
use Location;
use Main\Template\Page;
use Redirect;
use Request;
use SamPoyigi\Cart\Models\Orders_model;
use SamPoyigi\Pages\Models\Pages_model;
use Session;
use System\Classes\BaseComponent;

class Checkout extends BaseComponent
{
    use ValidatesForm;

    protected $sessionKey = 'sampoyigi.checkout.order.id';

    protected $order;

    public function defineProperties()
    {
        return [
            'orderDateFormat'  => [
                'label' => 'Date format to display order date on the checkout success page',
                'type'  => 'text',
            ],
            'orderTimeFormat'  => [
                'label' => 'Time format to display order time on the checkout success page',
                'type'  => 'text',
            ],
            'agreeTermsPage'   => [
                'label'   => 'lang:sampoyigi.cart::default.checkout.label_checkout_terms',
                'type'    => 'select',
                'options' => [static::class, 'getPagesOptions'],
                'comment' => 'lang:sampoyigi.cart::default.checkout.help_checkout_terms',
            ],
            'menusPage'        => [
                'label'   => 'lang:sampoyigi.cart::default.checkout.label_checkout_terms',
                'type'    => 'select',
                'default' => 'local/menus',
                'options' => [static::class, 'getPageOptions'],
                'comment' => 'Page to redirect to when checkout can not be performed.',
            ],
            'redirectPage'     => [
                'label'   => 'Page to redirect to when checkout fails',
                'type'    => 'select',
                'options' => [static::class, 'getPageOptions'],
                'default' => 'checkout/checkout',
            ],
            'ordersPage'       => [
                'label'   => 'Account orders page',
                'type'    => 'select',
                'options' => [static::class, 'getPageOptions'],
                'default' => 'account/orders',
            ],
            'successPage'      => [
                'label'   => 'Page to redirect to when checkout is successful',
                'type'    => 'select',
                'options' => [static::class, 'getPageOptions'],
                'default' => 'checkout/success',
            ],
            'successParamCode' => [
                'label'   => 'The parameter name used for the order hash code',
                'type'    => 'text',
                'default' => 'hash',
            ],
        ];
    }

    public static function getPageOptions()
    {
        return Page::lists('baseFileName', 'baseFileName');
    }

    public static function getPagesOptions()
    {
        return Pages_model::dropdown('name');
    }

    public function onRun()
    {
        $this->addJs('js/vendor/trigger.js', 'trigger-js');

        if ($this->isCheckoutSuccessPage())
            $this->clearCurrentOrderId();
        elseif (!$this->validateCart())
            return Redirect::to(restaurant_url($this->property('menusPage')));

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['orderDateFormat'] = $this->property('orderDateFormat', setting('date_format'));
        $this->page['orderTimeFormat'] = $this->property('orderTimeFormat', setting('time_format'));
        $this->page['agreeTermsPage'] = $this->property('agreeTermsPage');
        $this->page['redirectPage'] = $this->property('redirectPage');
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['ordersPage'] = $this->property('ordersPage');
        $this->page['successPage'] = $this->property('successPage');

        $this->page['confirmCheckoutEventHandler'] = $this->getEventHandler('onConfirm');

        $this->page['order'] = $this->getOrder();
        $this->page['orderTotal'] = Cart::total();
        $this->page['orderType'] = Location::orderType();
        $this->page['paymentGateways'] = Location::current()->listAvailablePayments();
    }

    /**
     * @return Orders_model
     */
    public function getOrder()
    {
        if (!is_null($this->order))
            return $this->order;

        if ($this->isCheckoutSuccessPage()) {
            $order = $this->getOrderByHash();
        }
        else {
            $order = Orders_model::find($this->getCurrentOrderId());
        }

        $customer = $this->customer();
        $customerId = $customer ? $customer->customer_id : null;

        // Only users can view their own orders
        if (!$order OR $order->customer_id != $customerId)
            $order = Orders_model::make($this->getDefaultAttributes());

        $this->setDefaultAttributes($order);

        return $this->order = $order;
    }

    public function customer()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }

    public function hashCode()
    {
        $routeParameter = $this->property('successParamCode');

        if ($code = $this->param($routeParameter)) {
            return $code;
        }

        return get('success');
    }

    public function onConfirm()
    {
        try {
            $data = post();

            $this->validateCart(TRUE);

            $this->validate($data, $this->createRules());

            if ($address = array_get($data, 'address', []))
                $this->validateAddress($address);

            $order = $this->createOrder($data);

            $data['cancelPage'] = $this->property('redirectPage');
            $data['successPage'] = $successPage = $this->property('successPage');
//            activity()
//                ->causedBy(Auth::getUser())
//                ->log(lang('system::activities.activity_logged_in'));

            $paymentMethod = Payments_model::whereCode($order->payment)->first();
            if ($order->payment AND (!$paymentMethod OR !$paymentMethod->status))
                throw new ApplicationException('Selected payment method is inactive, try a different one.');

            if (($redirect = $paymentMethod->processPaymentForm($data, $paymentMethod, $order)) === FALSE)
                return;

            if ($redirect instanceof RedirectResponse)
                return $redirect;

            if (!$successPage)
                return;

            return Redirect::to($this->pageUrl($successPage, ['hash' => $order->hash]));
        }
        catch (Exception $ex) {
            flash()->warning($ex->getMessage());

            return Redirect::back()->withInput();
        }
    }

    /**
     * Set the current order id.
     *
     * @param $orderId
     */
    public function setCurrentOrderId($orderId)
    {
        Session::put($this->sessionKey, $orderId);
    }

    /**
     * Get the current order id.
     *
     * @return mixed
     */
    public function getCurrentOrderId()
    {
        return Session::get($this->sessionKey);
    }

    /**
     * Clear the current order id.
     */
    public function clearCurrentOrderId()
    {
        Session::forget($this->sessionKey);
        Cart::destroy();
    }

    /**
     * Check if the given order id is the current order id.
     *
     * @param $orderId
     *
     * @return bool
     */
    public function isCurrentOrderId($orderId)
    {
        return $this->getCurrentOrderId() == $orderId;
    }

    protected function validateCart($throwException = FALSE)
    {
        try {
            if (!Cart::count())
                throw new ApplicationException(lang('sampoyigi.cart::default.checkout.alert_no_menu_to_order'));

            if (!$location = Location::current())
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_location_required'));

            if (Location::isClosed())
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_location_closed'));

            if (!Location::checkOrderType($orderType = Location::orderType()))
                throw new ApplicationException(lang('sampoyigi.cart::default.alert_'.$orderType.'_unavailable'));

            $orderDateTime = Location::orderDateTime();
            if (!$orderDateTime OR !Location::checkOrderTime($orderDateTime))
                throw new ApplicationException(lang('sampoyigi.cart::default.checkout.alert_no_delivery_time'));

            if ($orderType == 'delivery' AND Location::requiresUserPosition() AND !Location::userPosition()->isValid())
                throw new ApplicationException(lang('sampoyigi.local::default.alert_no_search_query'));
        }
        catch (Exception $ex) {
            if ($throwException)
                throw $ex;

            flash()->warning($ex->getMessage())->now();

            return FALSE;
        }

        if ($orderType == 'delivery' AND !Location::checkMinimumOrder(Cart::total()))
            return FALSE;

        return TRUE;
    }

    protected function validateAddress($address)
    {
        $address['country'] = app('country')->getCountryNameById($address['country_id']);
        $address = implode(" ", array_only($address, ['address_1', 'address_2', 'city', 'state', 'postcode', 'country']));

        $userPosition = app('geocoder')->geocode(['address' => $address]);
        if (!$userPosition OR !$userPosition->isValid())
            throw new ApplicationException(lang('sampoyigi.local::default.alert_invalid_search_query'));

        if (!$area = Location::current()->filterDeliveryArea($userPosition))
            throw new ApplicationException(lang('sampoyigi.cart::default.checkout.error_covered_area'));

        if (!Location::isCurrentAreaId($area->area_id)) {
            Location::setCoveredArea($area);
            throw new ApplicationException(lang('sampoyigi.cart::default.checkout.alert_delivery_area_changed'));
        }
    }

    protected function createRules()
    {
        $namedRules = [
            ['first_name', 'lang:sampoyigi.cart::default.checkout.label_first_name', 'required|min:2|max:32'],
            ['last_name', 'lang:sampoyigi.cart::default.checkout.label_last_name', 'required|min:2|max:32'],
            ['email', 'lang:sampoyigi.cart::default.checkout.label_email', 'sometimes|required|email|max:96|unique:customers'],
            ['telephone', 'lang:sampoyigi.cart::default.checkout.label_telephone', ''],
            ['comment', 'lang:sampoyigi.cart::default.checkout.label_comment', 'max:500'],
            ['payment', 'lang:sampoyigi.cart::default.checkout.label_payment_method', 'required|alpha_dash'],

            ['terms_condition', 'lang:button_agree_terms', 'sometimes|integer'],
        ];

        $orderType = Location::orderType();
        if ($orderType == 'delivery') {
            $namedRules[] = ['address_id', 'lang:sampoyigi.cart::default.checkout.label_address', 'integer'];
            $namedRules[] = ['address.address_1', 'lang:sampoyigi.cart::default.checkout.label_address_1', 'required|min:3|max:128'];
            $namedRules[] = ['address.city', 'lang:sampoyigi.cart::default.checkout.label_city', 'required|min:2|max:128'];
            $namedRules[] = ['address.state', 'lang:sampoyigi.cart::default.checkout.label_state', 'max:128'];
            $namedRules[] = ['address.postcode', 'lang:sampoyigi.cart::default.checkout.label_postcode', 'required|min:2|max:10'];
            $namedRules[] = ['address.country_id', 'lang:sampoyigi.cart::default.checkout.label_country', 'required|integer'];
        }

        return $namedRules;
    }

    /**
     * @param $data
     *
     * @return Orders_model
     */
    protected function createOrder($data)
    {
        $order = $this->getOrder();
        $customerId = ($customer = Auth::customer()) ? $customer->getKey() : null;

        if ($customer)
            $data['email'] = $customer->email;

        $addressId = null;
        if ($address = array_get($data, 'address', [])) {
            $address['customer_id'] = $customerId;
            $address['address_id'] = $order->address_id;
            $addressId = Addresses_model::createOrUpdateFromRequest($address)->getKey();
        }

        $order->fill($data);
        $order->address_id = $addressId;
        $order->customer_id = $customerId;
        $this->setDefaultAttributes($order);
        $order->save();

        $order->addOrderMenus(Cart::content()->toArray());
        $order->addOrderTotals($this->buildCartTotalsArray());

        $couponCondition = Cart::getCondition('coupon');
        if ($couponCondition AND $couponModel = $couponCondition->getModel())
            $order->logCouponHistory($couponModel, $customer);

        $this->setCurrentOrderId($order->order_id);

        return $order;
    }

    protected function getDefaultAttributes()
    {
        $customer = Auth::getUser();

        return [
            'first_name' => $customer ? $customer->first_name : null,
            'last_name'  => $customer ? $customer->last_name : null,
            'email'      => $customer ? $customer->email : null,
            'telephone'  => $customer ? $customer->telephone : null,
        ];
    }

    public function setDefaultAttributes($order)
    {
        $order->location_id = Location::current()->getKey();

        $order->order_type = Location::orderType();

        $orderDateTime = Location::orderDateTime();
        $order->order_date = $orderDateTime->format('Y-m-d');
        $order->order_time = $orderDateTime->format('H:i');

        $order->total_items = Cart::count();
        $order->cart = Cart::content();
        $order->order_total = Cart::total();

        $order->ip_address = Request::getClientIp();
    }

    protected function buildCartTotalsArray()
    {
        $totals = [
            [
                'code'     => 'subtotal',
                'title'    => lang('sampoyigi.cart::default.text_sub_total'),
                'value'    => Cart::subtotal(),
                'priority' => 0,
            ],
            [
                'code'     => 'total',
                'title'    => lang('sampoyigi.cart::default.text_order_total'),
                'value'    => Cart::total(),
                'priority' => 999,
            ],
        ];

        foreach (Cart::conditions() as $name => $condition) {
            $totals[] = [
                'code'     => $name,
                'title'    => $condition->getLabel(),
                'value'    => $condition->calculatedValue(),
                'priority' => $condition->getPriority(),
            ];
        }

        return $totals;
    }

    protected function isCheckoutSuccessPage()
    {
        return $this->page->getBaseFileName() == $this->property('successPage');
    }

    protected function getOrderByHash()
    {
        return Orders_model::whereHash($this->hashCode())->first();
    }
}