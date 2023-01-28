<?php

namespace Igniter\Cart\Classes;

use Admin\Models\Addresses_model;
use Admin\Models\Customers_model;
use Admin\Models\Orders_model;
use Igniter\Flame\Cart\CartCondition;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Traits\Singleton;
use Igniter\Local\Classes\CoveredArea;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Main\Facades\Auth;
use System\Traits\SessionMaker;

class OrderManager
{
    use Singleton;
    use SessionMaker;

    protected $sessionKey = 'igniter.checkout.order';

    /**
     * @var \Igniter\Flame\Cart\Cart
     */
    protected $cart;

    /**
     * @var \Igniter\Local\Classes\Location
     */
    protected $location;

    /**
     * @var \Admin\Models\Customers_model
     */
    protected $customer;

    public function initialize()
    {
        $this->cart = CartManager::instance()->getCart();
        $this->location = App::make('location');
        $this->customer = Auth::customer();
    }

    public function setCustomer(Customers_model $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    public function getCustomerId()
    {
        if (!$this->customer)
            return null;

        return $this->customer->getKey();
    }

    public function getOrder()
    {
        return $this->loadOrder();
    }

    /**
     * @return \Admin\Models\Orders_model
     */
    public function loadOrder()
    {
        $id = $this->getCurrentOrderId();

        $customerId = $this->customer
            ? $this->customer->customer_id
            : null;

        $order = Orders_model::find($id);

        // Only users can view their own orders
        if (!$order || $order->customer_id != $customerId)
            $order = Orders_model::make($this->getCustomerAttributes());

        return $order;
    }

    /**
     * @param $hash
     * @param \Admin\Models\Customers_model|null $customer
     * @return \Admin\Models\Orders_model|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOrderByHash($hash, $customer = null)
    {
        $query = Orders_model::whereHash($hash);

        if (!is_null($customer))
            $query->where('customer_id', $customer->getKey());

        return $query->first();
    }

    public function getDefaultPayment()
    {
        return $this->getPaymentGateways()->where('is_default', true)->first();
    }

    /**
     * @param $code
     * @return \Admin\Models\Payments_model|\Admin\Classes\BasePaymentGateway
     */
    public function getPayment($code)
    {
        return $this->getPaymentGateways()->where('code', $code)->first();
    }

    public function getPaymentGateways()
    {
        return $this->location->current()->listAvailablePayments()->sortBy('priority');
    }

    public function findDeliveryAddress($addressId)
    {
        if (empty($addressId))
            return null;

        return Addresses_model::find($addressId);
    }

    //
    //
    //

    public function validateCustomer($customer)
    {
        if (!$this->location->current()->allowGuestOrder() && (!$customer || !$customer->is_activated))
            throw new ApplicationException(lang('igniter.cart::default.checkout.alert_customer_not_logged'));
    }

    public function validateDeliveryAddress(array $address)
    {
        if (!array_get($address, 'country') && isset($address['country_id']))
            $address['country'] = app('country')->getCountryNameById($address['country_id']);

        $addressString = implode(' ', array_only($address, [
            'address_1', 'address_2', 'city', 'state', 'postcode', 'country',
        ]));

        if (!$this->location->requiresUserPosition())
            return;

        $collection = app('geocoder')->geocode($addressString);
        if (!$collection || $collection->isEmpty())
            throw new ApplicationException(lang('igniter.local::default.alert_invalid_search_query'));

        $userLocation = $collection->first();

        $this->location->updateUserPosition($userLocation);

        if (!$area = $this->location->current()->searchDeliveryArea($userLocation->getCoordinates()))
            throw new ApplicationException(lang('igniter.cart::default.checkout.error_covered_area'));

        if (!$this->location->isCurrentAreaId($area->area_id)) {
            $this->location->setCoveredArea(new CoveredArea($area));
            throw new ApplicationException(lang('igniter.cart::default.checkout.alert_delivery_area_changed'));
        }
    }

    /**
     * @param $order \Admin\Models\Orders_model
     * @param $data
     *
     * @return \Admin\Models\Orders_model
     */
    public function saveOrder($order, array $data)
    {
        Event::fire('igniter.checkout.beforeSaveOrder', [$order, $data]);

        if ($this->customer)
            $data['email'] = $this->customer->email;

        $addressId = null;
        if ($address = array_get($data, 'address', [])) {
            $address['customer_id'] = $this->getCustomerId();

            $addressId = array_get($data, 'address_id');
            $addressId = !empty($addressId) ? $addressId : Addresses_model::createOrUpdateFromRequest($address)->getKey();

            // Update customer default address
            if ($this->customer) {
                $this->customer->address_id = $addressId;
                $this->customer->save();
            }
        }

        $order->fill($data);
        $order->address_id = $addressId;
        $this->applyRequiredAttributes($order);

        $order->save();

        $this->setCurrentOrderId($order->order_id);

        $order->addOrderMenus($this->cart->content()->all());
        $order->addOrderTotals($this->getCartTotals());

        Event::fire('igniter.checkout.afterSaveOrder', [$order]);

        return $order;
    }

    public function processPayment($order, array $data)
    {
        Event::fire('igniter.checkout.beforePayment', [$order, $data]);

        if (!strlen($order->payment) && $this->processPaymentLessForm($order))
            return true;

        $paymentMethod = $this->getPayment($order->payment);
        if (!$paymentMethod || !$paymentMethod->status)
            throw new ApplicationException('Selected payment method is inactive, try a different one.');

        if (!$paymentMethod->isApplicable($order->order_total, $paymentMethod))
            throw new ApplicationException(sprintf(
                lang('igniter.payregister::default.alert_min_order_total'),
                currency_format($paymentMethod->order_total),
                $paymentMethod->name
            ));

        if ($paymentMethod->hasApplicableFee() && !optional($this->cart->getCondition('paymentFee'))->isApplied()) {
            throw new ApplicationException(sprintf(
                lang('igniter.payregister::default.alert_missing_applicable_fee'),
                $paymentMethod->name
            ));
        }

        if (array_get($data, 'pay_from_profile') == 1) {
            $result = $paymentMethod->payFromPaymentProfile($order, $data);
        }
        else {
            $result = $paymentMethod->processPaymentForm($data, $paymentMethod, $order);
        }

        return $result;
    }

    public function applyRequiredAttributes($order)
    {
        $order->customer_id = $this->customer ? $this->customer->getKey() : null;
        $order->location_id = $this->location->current()->getKey();
        $order->order_type = $this->location->orderType();

        $this->applyOrderDateTime($order);

        $order->total_items = $this->cart->count();
        $order->cart = $this->cart->content();
        $order->order_total = $this->cart->total();

        $paymentCode = $this->getCurrentPaymentCode();
        $order->payment = $order->order_total > 0 ? $paymentCode : '';

        $this->applyCurrentPaymentFee($order->payment);

        $order->ip_address = Request::getClientIp();
    }

    protected function getCustomerAttributes()
    {
        $customer = $this->customer;

        return [
            'first_name' => $customer ? $customer->first_name : null,
            'last_name' => $customer ? $customer->last_name : null,
            'email' => $customer ? $customer->email : null,
            'telephone' => $customer ? $customer->telephone : null,
            'address_id' => $customer ? $customer->address_id : null,
        ];
    }

    public function getCartTotals()
    {
        $totals = $this->cart->conditions()->map(function (CartCondition $condition) {
            return [
                'code' => $condition->name,
                'title' => $condition->getLabel(),
                'value' => $condition->getValue(),
                'priority' => $condition->getPriority() ?: 1,
                'is_summable' => !$condition->isInclusive(),
            ];
        })->all();

        $totals['subtotal'] = [
            'code' => 'subtotal',
            'title' => lang('igniter.cart::default.text_sub_total'),
            'value' => $this->cart->subtotal(),
            'priority' => 0,
            'is_summable' => false,
        ];

        $totals['total'] = [
            'code' => 'total',
            'title' => lang('igniter.cart::default.text_order_total'),
            'value' => max(0, $this->cart->total()),
            'priority' => 999,
            'is_summable' => false,
        ];

        return $totals;
    }

    /**
     * @param \Admin\Models\Orders_model $order
     * @return bool
     */
    protected function processPaymentLessForm($order)
    {
        if ($order->order_total > 0)
            return false;

        $order->updateOrderStatus(setting('default_order_status'), ['notify' => false]);
        $order->markAsPaymentProcessed();

        return true;
    }

    protected function applyOrderDateTime($order)
    {
        if (!$orderDateTime = $this->location->orderDateTime())
            return;

        if ($isAsapTime = $this->location->orderTimeIsAsap())
            $orderDateTime->addMinutes($this->location->orderLeadTime());

        $order->order_time_is_asap = $isAsapTime;
        $order->order_date = $orderDateTime->format('Y-m-d');
        $order->order_time = $orderDateTime->format('H:i');
    }

    //
    // Session
    //

    public function clearOrder()
    {
        $this->location->updateScheduleTimeSlot(null);
        $this->cart->destroy($this->getCustomerId());
        $this->resetSession();
    }

    /**
     * Set the current order id.
     *
     * @param $orderId
     */
    public function setCurrentOrderId($orderId)
    {
        $this->putSession('id', $orderId);
    }

    /**
     * Get the current order id.
     *
     * @return mixed
     */
    public function getCurrentOrderId()
    {
        return $this->getSession('id');
    }

    /**
     * Clear the current order id.
     */
    public function clearCurrentOrderId()
    {
        $this->forgetSession('id');
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

    public function setCurrentPaymentCode($code)
    {
        $this->putSession('paymentCode', $code);
    }

    public function getCurrentPaymentCode()
    {
        return $this->getSession('paymentCode') ?: optional($this->getDefaultPayment() ?: $this->getPaymentGateways()->first())->code;
    }

    public function applyCurrentPaymentFee($code)
    {
        $this->setCurrentPaymentCode($code);

        if (!$condition = $this->cart->getCondition('paymentFee'))
            return;

        $condition->setMetaData(['code' => $code]);

        $this->cart->loadCondition($condition);

        return $condition;
    }
}
