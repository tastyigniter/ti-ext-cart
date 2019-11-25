<?php namespace Igniter\Cart\Components;

use Admin\Models\Orders_model;
use Auth;
use Cart;
use Igniter\Cart\Classes\OrderManager;
use Igniter\Cart\Models\Menus_model;
use Illuminate\Contracts\Support\Arrayable;
use Redirect;

class Order extends \System\Classes\BaseComponent
{
    /**
     * @var \Igniter\Cart\Classes\OrderManager
     */
    protected $orderManager;

    public function initialize()
    {
        $this->orderManager = OrderManager::instance();
    }

    public function defineProperties()
    {
        return [
            'ordersPage' => [
                'label' => 'Account Orders Page',
                'type' => 'string',
                'default' => 'account/orders',
            ],
            'menusPage' => [
                'label' => 'Menus Page, page to redirect to when a user clicks the re-order button',
                'type' => 'string',
                'default' => 'local/menus',
            ],
            'orderDateTimeFormat' => [
                'label' => 'Date time format to display order date time',
                'type' => 'text',
                'default' => 'DD MMM \a\t HH:mm',
            ],
            'hideReorderBtn' => [
                'label' => 'Whether to hide the reorder button, should be enabled on the checkout success page',
                'type' => 'switch',
                'default' => FALSE,
            ],
            'hashParamName' => [
                'label' => 'The parameter name used for the order hash code',
                'type' => 'text',
                'default' => 'hash',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['ordersPage'] = $this->property('ordersPage');
        $this->page['hideReorderBtn'] = $this->property('hideReorderBtn');
        $this->page['showReviews'] = setting('allow_reviews') == 1;
        $this->page['orderDateTimeFormat'] = $this->property('orderDateTimeFormat');

        $this->page['hashParam'] = $this->param('hash');
        $this->page['order'] = $order = $this->getOrder();

        if (!$order OR !$order->isPaymentProcessed())
            return Redirect::to($this->property('ordersPage'));

        if ($this->orderManager->isCurrentOrderId($order->order_id))
            $this->orderManager->clearOrder();
    }

    public function onReOrder()
    {
        if (!is_numeric($orderId = input('orderId')))
            return;

        if (!$order = Orders_model::find($orderId))
            return;

        $orderMenus = $order->getOrderMenus();

        foreach ($orderMenus as $orderMenu) {
            if (!$menuModel = Menus_model::find($orderMenu->menu_id))
                continue;

            if (is_string($orderMenu->option_values))
                $orderMenu->option_values = @unserialize($orderMenu->option_values);

            if ($orderMenu->option_values instanceof Arrayable)
                $orderMenu->option_values = $orderMenu->option_values->toArray();

            Cart::add($menuModel, $orderMenu->quantity, $orderMenu->option_values, $orderMenu->comment);
        }

        flash()->success(sprintf(
            lang('igniter.cart::default.orders.alert_reorder_success'), $orderId
        ));

        $menusPage = $this->property('menusPage');

        return Redirect::to($this->controller->pageUrl($menusPage, [
            'orderId' => $orderId,
            'location' => $order->location->permalink_slug,
        ]));
    }

    protected function getOrder()
    {
        if (!is_string($hashParam = $this->getHashParam()))
            return null;

        return $this->orderManager->getOrderByHash($hashParam, Auth::customer());
    }

    protected function getHashParam()
    {
        return $this->param($this->property('hashParamName'));
    }
}