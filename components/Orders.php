<?php namespace Igniter\Cart\Components;

use Admin\Models\Orders_model;
use Auth;
use Cart;
use Igniter\Cart\Models\Menus_model;
use Redirect;

class Orders extends \System\Classes\BaseComponent
{
    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'label' => 'Page Number',
                'type' => 'string',
            ],
            'itemsPerPage' => [
                'label' => 'Items Per Page',
                'type' => 'number',
                'default' => 20,
            ],
            'sortOrder' => [
                'label' => 'Sort order',
                'type' => 'string',
            ],
            'addReviewsPage' => [
                'label' => 'Add review page',
                'type' => 'string',
                'default' => 'account/reviews',
            ],
            'ordersPage' => [
                'label' => 'Account Orders Page',
                'type' => 'string',
                'default' => 'account/orders',
            ],
            'reorderPage' => [
                'label' => 'Re Order Page',
                'type' => 'string',
                'default' => 'local/menus',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['ordersPage'] = $this->property('ordersPage');
        $this->page['addReviewsPage'] = $this->property('addReviewsPage');
        $this->page['showReviews'] = setting('allow_reviews') == 1;
        $this->page['customerOrders'] = $this->loadOrders();

        $this->page['orderIdParam'] = $paramId = $this->param('orderId');
        $this->page['customerOrder'] = $order = $this->getOrder();
        if ($paramId AND !$order)
            return Redirect::to($this->property('ordersPage'));
    }

    protected function getOrder()
    {
        if (!is_numeric($orderIdParam = $this->param('orderId')))
            return null;

        $customer = Auth::customer();
        $order = Orders_model::find($orderIdParam);
        if (!$customer OR $order->customer_id != $customer->customer_id)
            return null;

        return $order;
    }

    public function onReOrder()
    {
        if (is_numeric($orderId = input('orderId'))) {
            $order = Orders_model::find($orderId);
            if ($order AND $orderMenus = $order->getOrderMenus()) {
                foreach ($orderMenus as $menu) {
                    if (!$menuModel = Menus_model::find($menu->menu_id))
                        continue;

                    if (!is_array($menu->option_values))
                        $menu->option_values = @unserialize($menu->option_values);

                    Cart::add($menuModel, $menu->quantity, $menu->option_values, $menu->comment);
                }

                flash()->success(sprintf(
                    lang('igniter.cart::default.orders.alert_reorder_success'), $orderId
                ));

                $reorderPage = $this->property('reorderPage');

                return Redirect::to($this->controller->pageUrl($reorderPage, [
                    'orderId' => $orderId,
                    'location' => $order->location->permalink_slug,
                ]));
            }
        }
    }

    protected function loadOrders()
    {
        if (!$customer = Auth::customer())
            return [];

        return Orders_model::with(['location', 'status'])->listFrontEnd([
            'page' => $this->param('page'),
            'pageLimit' => $this->property('itemsPerPage'),
            'sort' => $this->property('sortOrder', 'date_added desc'),
            'customer' => $customer,
        ]);
    }
}