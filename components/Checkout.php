<?php

namespace SamPoyigi\Cart\Components;

use Admin\Models\Addresses_model;
use Admin\Models\Pages_model;
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
            'dateFormat'     => [
                'label'   => 'Date format',
                'type'    => 'text',
                'default' => 'D d',
            ],
            'hourFormat'     => [
                'label'   => 'Hour format',
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
            'successPage'    => [
                'label'   => 'Page to redirect to when checkout is successful',
                'type'    => 'text',
                'default' => 'checkout/success',
            ],
        ];
    }

    public function getAgreeTermsPageDropdown()
    {
        return Pages_model::getDropdownOptions();
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

    public function onRun()
    {
        $this->addJs(assets_url('js/app/trigger.js'), 'trigger-js');
        $this->addJs('vendor/moment.min.js', 'checkout-moment-js');
        $this->addJs('js/checkout.timepicker.js', 'checkout-timepicker-js');

        $this->prepareVars();

        if (!$this->validateCart())
            return Redirect::to(restaurant_url($this->property('menusPage')));

        $this->page['order'] = $order = $this->getOrder();
        $this->page['orderType'] = Location::orderType();
        $this->page['orderTimeRange'] = $this->getOrderTimes();
        $this->page['paymentGateways'] = location::current()->listAvailablePayments();
    }

    protected function prepareVars()
    {
        $this->page['orderDateFormat'] = $this->property('dateFormat');
        $this->page['orderHourFormat'] = $this->property('hourFormat');
        $this->page['agreeTermsPage'] = $this->property('agreeTermsPage');
        $this->page['redirectPage'] = $this->property('redirectPage');
        $this->page['successPage'] = $this->property('successPage');

        $this->page['confirmCheckoutEventHandler'] = $this->getEventHandler('onConfirm');
    }

    /**
     * @return Orders_model
     */
    public function getOrder()
    {
        if (!is_null($this->order))
            return $this->order;

        $id = $this->getCurrentOrderId();

        $order = Orders_model::find($id);

        $user = Auth::getUser();
        $customerId = $user ? $user->customer_id : null;

        // Only orders without a status can be confirmed
        // Only users can view their own orders
        if (!$order OR $order->isPlaced() OR $order->customer_id != $customerId)
            $order = Orders_model::make();

        if ($order)
            $order->setReceiptPageName($this->property('successPage'));

        return $this->order = $order;
    }

    public function getOrderTimes()
    {
        $generated = [];
        $timeInterval = Location::orderTimeInterval();
        $periods = Location::orderTimePeriods();
        foreach ($periods as $date => $workingHours) {
            if ($workingHours->isClosed())
                continue;

            $weekDate = $workingHours->getWeekDate()->format($this->property('dateFormat'));
            if ($workingHours->open->isToday())
                $workingHours->opening_time = Carbon::now()->addMinutes($timeInterval)->format('H:i');

            foreach ($workingHours->generateTimes($timeInterval) as $dateTime) {
                $key = $dateTime->format($this->property('hourFormat'));
                $generated[$weekDate][$key][] = $dateTime->format('i');
            }
        }

        return $generated;
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

            // clear cart

            if (!$paymentMethod = Payments_model::whereCode($order->payment)->first())
                return;

            if (($redirect = $paymentMethod->processPaymentForm($data, $paymentMethod, $order)) === FALSE)
                return;

            if ($redirect instanceof RedirectResponse)
                return $redirect;

            if (!$returnPage = $order->getReceiptUrl())
                return;

            return Redirect::to($returnPage);
        } catch (Exception $ex) {
            flash()->warning($ex->getMessage());

            return Redirect::back()->withInput();
        }
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

        return TRUE;
    }

    protected function validateAddress($address)
    {
        $address['country'] = app('country')->getCountryNameById($address['country_id']);
        $address = implode(" ", array_only($address, ['address_1', 'address_2', 'city', 'state', 'postcode', 'country']));

        $userPosition = app('geocoder')->geocode(['address' => $address]);
        if (!$userPosition OR !$userPosition->isValid())
            throw new ValidationException(lang('sampoyigi.local::default.alert_invalid_search_query'));

        if (!$area = Location::current()->findDeliveryArea($userPosition))
            throw new ValidationException(lang('sampoyigi.cart::default.checkout.error_covered_area'));

        if (!Location::isCurrentAreaId($area->area_id)) {
            Location::setCoveredArea($area);
            throw new ApplicationException(lang('sampoyigi.cart::default.checkout.alert_delivery_area_changed'));
        }
    }

    /**
     * @param $data
     *
     * @return Orders_model
     */
    protected function createOrder($data)
    {
        $order = $this->getOrder();
        $customerId = ($user = Auth::getUser()) ? $user->getId() : null;

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
            "{$this->property('dateFormat')} {$this->property('hourFormat')} i",
            "{$orderDate} {$orderHour} {$orderMinute}");

        $order->fill($data);
        $order->address_id = $addressId;
        $order->customer_id = $customerId;
        $order->location_id = Location::current()->getKey();
        $order->order_type = Location::orderType();
        $order->order_date = $orderDateTime->format('Y-m-d');
        $order->order_time = $orderDateTime->format('H:i');
        $order->total_items = Cart::count();
        $order->cart = Cart::content();
        $order->order_total = Cart::total();
        $order->save();

        $order->addOrderMenus(Cart::content());
        $order->addOrderTotals(Cart::allTotals());

        $this->setCurrentOrderId($order->order_id);

        return $order;
    }

    protected function createRules()
    {
        $orderType = Location::orderType();
        $uniqueRule = Rule::unique('customers');
        if ($user = Auth::getUser())
            $uniqueRule->ignore($user->customer_id, 'customer_id');

        $namedRules = [
            ['first_name', 'lang:sampoyigi.cart::default.checkout.label_first_name', 'required|min:2|max:32'],
            ['last_name', 'lang:sampoyigi.cart::default.checkout.label_last_name', 'required|min:2|max:32'],
            ['email', 'lang:sampoyigi.cart::default.checkout.label_email', ['required', 'email', 'max:96', $uniqueRule]],
            ['telephone', 'lang:sampoyigi.cart::default.checkout.label_telephone', 'required|numeric'],
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
}