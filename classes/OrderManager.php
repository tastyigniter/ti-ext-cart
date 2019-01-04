<?php

namespace Igniter\Cart\Classes;

use Admin\Models\Addresses_model;
use Admin\Models\Payments_model;
use ApplicationException;
use Auth;
use Cart;
use Event;
use Igniter\Cart\Models\Orders_model;
use Igniter\Flame\Cart\CartCondition;
use Igniter\Flame\Traits\Singleton;
use Location;
use Request;
use Session;

class OrderManager
{
    use Singleton;

    protected $sessionKey = 'igniter.checkout.order.id';

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
        $this->cart = Cart::instance();
        $this->location = Location::instance();
        $this->customer = Auth::customer();
    }

    public function getCustomerId()
    {
        if (!$this->customer)
            return null;

        return $this->customer->getKey();
    }

    /**
     * @return \Igniter\Cart\Models\Orders_model
     */
    public function getOrder()
    {
        $id = $this->getCurrentOrderId();

        $customerId = $this->customer
            ? $this->customer->customer_id
            : null;

        $order = Orders_model::find($id);

        // Only users can view their own orders
        if (!$order OR $order->customer_id != $customerId)
            $order = Orders_model::make($this->getCustomerAttributes());

        return $order;
    }

    public function getOrderByHash($code)
    {
        return Orders_model::whereHash($code)->first();
    }

    /**
     * @param $code
     * @return \Admin\Models\Payments_model|\Admin\Classes\BasePaymentGateway
     */
    public function getPayment($code)
    {
        return Payments_model::whereCode($code)->first();
    }

    public function getPaymentGateways()
    {
        return $this->location->current()->listAvailablePayments();
    }

    /**
     * @param $order \Igniter\Cart\Models\Orders_model
     * @param $data
     *
     * @return Orders_model
     */
    public function saveOrder($order, $data)
    {
        Event::fire('igniter.checkout.beforeSaveOrder', [$order, $data]);

        $customerId = $this->getCustomerId();

        if ($this->customer)
            $data['email'] = $this->customer->email;

        $addressId = null;
        if ($address = array_get($data, 'address', [])) {
            $address['customer_id'] = $customerId;
            $address['address_id'] = $order->address_id;
            $addressId = Addresses_model::createOrUpdateFromRequest($address)->getKey();

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

        $order->addOrderMenus(Cart::content()->toArray());
        $order->addOrderTotals($this->getCartTotals());

        return $order;
    }

    public function processPayment($order, $data)
    {
        Event::fire('igniter.checkout.beforePayment', [$order, $data]);

        if (!strlen($order->payment))
            return;

        $paymentMethod = $this->getPayment($order->payment);
        if (!$paymentMethod OR !$paymentMethod->status)
            throw new ApplicationException('Selected payment method is inactive, try a different one.');

        $result = $paymentMethod->processPaymentForm($data, $paymentMethod, $order);

        return $result;
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

    public function applyRequiredAttributes($order)
    {
        $order->customer_id = $this->customer ? $this->customer->getKey() : null;
        $order->location_id = $this->location->current()->getKey();
        $order->order_type = $this->location->orderType();

        if ($orderDateTime = $this->location->orderDateTime()) {
            $order->order_date = $orderDateTime->format('Y-m-d');
            $order->order_time = $orderDateTime->format('H:i');
        }

        $order->total_items = $this->cart->count();
        $order->cart = $this->cart->content();
        $order->order_total = $this->cart->total();

        $order->ip_address = Request::getClientIp();
    }

    protected function getCartTotals()
    {
        $totals = $this->cart->conditions()->map(function (CartCondition $condition) {
            return [
                'code' => $condition->name,
                'title' => $condition->getLabel(),
                'value' => $condition->getValue(),
                'priority' => $condition->getPriority(),
            ];
        })->all();

        $totals['subtotal'] = [
            'code' => 'subtotal',
            'title' => lang('igniter.cart::default.text_sub_total'),
            'value' => $this->cart->subtotal(),
            'priority' => 0,
        ];

        $totals['total'] = [
            'code' => 'total',
            'title' => lang('igniter.cart::default.text_order_total'),
            'value' => $this->cart->total(),
            'priority' => 999,
        ];

        return $totals;
    }

    //
    // Session
    //

    public function clearOrder()
    {
        $this->cart->destroy($this->getCustomerId());
        $this->clearCurrentOrderId();
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
}