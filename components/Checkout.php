<?php

namespace SamPoyigi\Cart\Components;

use Admin\Models\Addresses_model;
use Admin\Models\Coupons_model;
use SamPoyigi\Pages\Models\Pages_model;
use Admin\Models\Payments_model;
use Admin\Traits\ValidatesForm;
use ApplicationException;
use Auth;
use Carbon\Carbon;
use Cart;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Location;
use Redirect;
use SamPoyigi\Cart\Models\Orders_model;
use Session;
use System\Classes\BaseComponent;
use ValidationException;

class Checkout extends BaseComponent
{
    use ValidatesForm;

    protected $sessionKey = 'sampoyigi.checkout.order.id';

    protected $order;

    public function defineProperties()
    {
        return [
            'dayFormat'     => [
                'label'   => 'Date format for the order times dropdown',
                'type'    => 'text',
                'default' => 'D d',
            ],
            'hourFormat'     => [
                'label'   => 'Hour format for the order times dropdown',
                'type'    => 'text',
                'default' => 'h a',
            ],
            'agreeTermsPage' => [
                'label'   => 'lang::sampoyigi.cart::default.checkout.label_checkout_terms',
                'type'    => 'select',
                'comment' => 'lang::sampoyigi.cart::default.checkout.help_checkout_terms',
            ],
            'menusPage'      => [
                'label'   => 'lang::sampoyigi.cart::default.checkout.label_checkout_terms',
                'type'    => 'select',
                'default' => 'local/menus',
                'comment' => 'Page to redirect to when checkout can not be performed.',
            ],
            'redirectPage'   => [
                'label'   => 'Page to redirect to when checkout fails',
                'type'    => 'text',
                'default' => 'checkout/checkout',
            ],
            'ordersPage'    => [
                'label'   => 'Account orders page',
                'type'    => 'text',
                'default' => 'account/orders',
            ],
            'successPage'    => [
                'label'   => 'Page to redirect to when checkout is successful',
                'type'    => 'text',
                'default' => 'checkout/success',
            ],
            'successParamCode' => [
                'label'   => 'The parameter name used for the order hash code',
                'type'    => 'text',
                'default' => 'hash',
            ],
        ];
    }

    public function getAgreeTermsPageDropdown()
    {
        return Pages_model::getDropdownOptions();
    }

    public function onRun()
    {
        $this->addJs(assets_url('js/app/trigger.js'), 'trigger-js');
        $this->addJs('vendor/moment.min.js', 'checkout-moment-js');
        $this->addJs('js/checkout.timepicker.js', 'checkout-timepicker-js');

        if ($this->isCheckoutSuccessPage())
            $this->clearCurrentOrderId();
        elseif (!$this->validateCart())
            return Redirect::to(restaurant_url($this->property('menusPage')));

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['orderDayFormat'] = $this->property('dayFormat');
        $this->page['orderHourFormat'] = $this->property('hourFormat');
        $this->page['orderDateFormat'] = setting('date_format');
        $this->page['orderTimeFormat'] = setting('time_format');
        $this->page['agreeTermsPage'] = $this->property('agreeTermsPage');
        $this->page['redirectPage'] = $this->property('redirectPage');
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['ordersPage'] = $this->property('ordersPage');
        $this->page['successPage'] = $this->property('successPage');

        $this->page['confirmCheckoutEventHandler'] = $this->getEventHandler('onConfirm');

        $this->page['order'] = $this->getOrder();
        $this->page['orderType'] = Location::orderType();
        $this->page['orderTimeRange'] = $this->getOrderTimes();
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
        } else {
            $order = Orders_model::find($this->getCurrentOrderId());
        }


        $customer = $this->customer();
        $customerId = $customer ? $customer->customer_id : null;

        // Only users can view their own orders
        if (!$order OR $order->customer_id != $customerId)
            $order = Orders_model::make($this->getDefaultAttributes());

        if ($order)
            $order->setReceiptPageName($this->property('successPage'));

        return $this->order = $order;
    }

    public function customer()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }

    public function getOrderTimes()
    {
        $generated = [];
        $timeInterval = Location::orderTimeInterval();
        $periods = Location::orderTimePeriods();
        foreach ($periods as $date => $workingHours) {
            if ($workingHours->isClosed())
                continue;

            $weekDate = $workingHours->getWeekDate()->format($this->property('dayFormat'));
            if ($workingHours->open->isToday())
                $workingHours->opening_time = Carbon::now()->addMinutes($timeInterval)->format('H:i');

            foreach ($workingHours->generateTimes($timeInterval) as $dateTime) {
                $key = $dateTime->format($this->property('hourFormat'));
                $generated[$weekDate][$key][] = $dateTime->format('i');
            }
        }

        return $generated;
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

            $this->validateCart(true);

            $this->validate($data, $this->createRules());

            if ($address = array_get($data, 'address', []))
                $this->validateAddress($address);

            $order = $this->createOrder($data);

//            activity()
//                ->causedBy(Auth::getUser())
//                ->log(lang('system::activities.activity_logged_in'));

            if ($paymentMethod = Payments_model::isEnabled()->whereCode($order->payment)->first()) {

                if (($redirect = $paymentMethod->processPaymentForm($data, $paymentMethod, $order)) === FALSE)
                    return;

                if ($redirect instanceof RedirectResponse)
                    return $redirect;
            }

            if (!$returnPage = $order->getReceiptUrl())
                return;

            return Redirect::to($returnPage);
        } catch (Exception $ex) {
            throw $ex;
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

    protected function validateCart($throwException = false)
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

            if ($orderType == 'delivery' AND Location::requiresUserPosition() AND !Location::userPosition()->isValid())
                throw new ApplicationException(lang('sampoyigi.local::default.alert_no_search_query'));
        } catch (Exception $ex) {
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
        $orderType = Location::orderType();

        $namedRules = [
            ['first_name', 'lang:sampoyigi.cart::default.checkout.label_first_name', 'required|min:2|max:32'],
            ['last_name', 'lang:sampoyigi.cart::default.checkout.label_last_name', 'required|min:2|max:32'],
            ['email', 'lang:sampoyigi.cart::default.checkout.label_email', 'sometimes|required|email|max:96|unique:customers'],
            ['telephone', 'lang:sampoyigi.cart::default.checkout.label_telephone', ''],
            ['comment', 'lang:sampoyigi.cart::default.checkout.label_comment', 'max:500'],
            ['payment', 'lang:sampoyigi.cart::default.checkout.label_payment_method', 'required|alpha_dash'],

            ['asap', sprintf(lang('sampoyigi.cart::default.checkout.label_order_time_type'), $orderType), 'required|integer'],
            ['order_date', sprintf(lang('sampoyigi.cart::default.checkout.label_order_time'), $orderType), 'required'],
            ['order_hour', 'lang:sampoyigi.cart::default.checkout.label_hour', 'required'],
            ['order_minute', 'lang:sampoyigi.cart::default.checkout.label_minute', 'required'],
            ['terms_condition', 'lang:button_agree_terms', 'sometimes|integer'],
        ];

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
            $addressId = Addresses_model::createOrUpdateFromPost($address)->getKey();
        }

        $orderDate = array_get($data, 'order_date');
        $orderHour = array_get($data, 'order_hour');
        $orderMinute = array_get($data, 'order_minute');
        $orderDateTime = Carbon::createFromFormat(
            "{$this->property('dayFormat')} {$this->property('hourFormat')} i",
            "{$orderDate} {$orderHour} {$orderMinute}");

        $order->fill($data);
        $order->address_id = $addressId;
        $order->customer_id = $customerId;
        $order->location_id = Location::current()->getKey();
        $order->order_type = Location::orderType();
        $order->order_date = $orderDateTime->format('Y-m-d');
        $order->order_time = $orderDateTime->format('H:i');
        $order->total_items = Cart::count();
        $order->cart = $content = Cart::content();
        $order->order_total = Cart::total();
        $order->save();

        $order->addOrderMenus($content->toArray());
        $order->addOrderTotals(Cart::allTotals());

        if ($couponCondition = Cart::getConditionByName('coupon')) {
            $code = $couponCondition->getMetaData('code');
            if ($code AND $coupon = Coupons_model::whereCode($code)->first())
                $order->addOrderCoupon($coupon, $customer);
        }

        $this->setCurrentOrderId($order->order_id);

        $order->setOrderViewPageName($this->property('ordersPage'));

        return $order;
    }

    protected function getDefaultAttributes()
    {
        $customer = Auth::getUser();

        return [
            'first_name' => $customer ? $customer->first_name : null,
            'last_name' => $customer ? $customer->last_name : null,
            'email' => $customer ? $customer->email : null,
            'telephone' => $customer ? $customer->telephone : null,
        ];
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