<?php

namespace Igniter\Cart\Components;

use Admin\Models\Orders_model;
use Admin\Traits\ValidatesForm;
use Exception;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Classes\OrderManager;
use Igniter\Cart\Models\Menus_model;
use Igniter\Flame\Cart\Facades\Cart;
use Igniter\Flame\Exception\ApplicationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Redirect;
use Main\Facades\Auth;
use Main\Traits\UsesPage;

class Order extends \System\Classes\BaseComponent
{
    use ValidatesForm;
    use UsesPage;

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
                'type' => 'select',
                'default' => 'account'.DIRECTORY_SEPARATOR.'orders',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'menusPage' => [
                'label' => 'Menus Page, page to redirect to when a user clicks the re-order button',
                'type' => 'select',
                'default' => 'local'.DIRECTORY_SEPARATOR.'menus',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'hideReorderBtn' => [
                'label' => 'Whether to hide the reorder button, should be hidden on the checkout success page',
                'type' => 'switch',
                'default' => false,
                'validationRule' => 'required|boolean',
            ],
            'hashParamName' => [
                'label' => 'The parameter name used for the order hash code',
                'type' => 'text',
                'default' => 'hash',
                'validationRule' => 'required|regex:/^[a-z0-9]+$/i',
            ],
        ];
    }

    public function getStatusWidthForProgressBars()
    {
        $result = [];

        $order = $this->getOrder();

        $result['default'] = 0;
        $result['processing'] = 0;
        $result['completed'] = 0;

        if ($order->status_id == setting('default_order_status')) {
            $result['default'] = 50;
        }

        if (in_array($order->status_id, setting('processing_order_status', []))) {
            $result['default'] = 100;
            $result['processing'] = 50;
        }

        if (in_array($order->status_id, setting('completed_order_status', []))) {
            $result['default'] = 100;
            $result['processing'] = 100;
            $result['completed'] = 100;
        }

        return $result;
    }

    public function showCancelButton($order = null)
    {
        if (is_null($order) && !$order = $this->getOrder())
            return false;

        if (!setting('canceled_order_status') || $order->isCanceled())
            return false;

        return $order->isCancelable();
    }

    public function onRun()
    {
        $this->page['ordersPage'] = $this->property('ordersPage');
        $this->page['hideReorderBtn'] = $this->property('hideReorderBtn');
        $this->page['orderDateTimeFormat'] = lang('system::lang.moment.date_time_format_short');

        $this->page['hashParam'] = $this->param('hash');
        $this->page['order'] = $order = $this->getOrder();

        $this->addJs('js/order.js', 'checkout-js');

        if (!$order || !$order->isPaymentProcessed())
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

        foreach ($order->getOrderMenus() as $orderMenu) {
            if (!$menuModel = Menus_model::findBy($orderMenu->menu_id))
                continue;

            $this->addCartItem($menuModel, $orderMenu);
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

    public function onCancel()
    {
        $validated = $this->validate(request()->input(), [
            'orderId' => ['required', 'numeric'],
            'cancel_reason' => ['string', 'max:255'],
        ]);

        if (!$order = Orders_model::find($validated['orderId']))
            return;

        if (!$this->showCancelButton($order))
            throw new ApplicationException(lang('igniter.cart::default.orders.alert_cancel_failed'));

        if (!$order->markAsCanceled([
            'comment' => array_get($validated, 'cancel_reason'),
            'notify' => true,
        ])) throw new ApplicationException(lang('igniter.cart::default.orders.alert_cancel_failed'));

        flash()->success(lang('igniter.cart::default.orders.alert_cancel_success'));

        return redirect()->back();
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

    protected function addCartItem($menuModel, $orderMenu): void
    {
        try {
            CartManager::instance()->validateCartMenuItem($menuModel, $orderMenu->quantity);

            if (is_string($orderMenu->option_values))
                $orderMenu->option_values = @unserialize($orderMenu->option_values);

            if ($orderMenu->option_values instanceof Arrayable)
                $orderMenu->option_values = $orderMenu->option_values->toArray();

            $options = $this->prepareCartItemOptions($menuModel, $orderMenu->option_values);

            Cart::add($menuModel, $orderMenu->quantity, $options, $orderMenu->comment);
        } catch (Exception $ex) {
            flash()->warning($ex->getMessage());
        }
    }

    protected function prepareCartItemOptions($menuModel, $optionValues)
    {
        $options = [];
        foreach ($optionValues as $cartOption) {
            if (!$menuOption = $menuModel->menu_options->keyBy('menu_option_id')->get($cartOption['id']))
                continue;

            try {
                CartManager::instance()->validateMenuItemOption($menuOption, $cartOption['values']->toArray());

                $cartOption['values'] = $cartOption['values']->filter(function ($cartOptionValue) use ($menuOption) {
                    return $menuOption->menu_option_values->keyBy('menu_option_value_id')->has($cartOptionValue->id);
                })->toArray();

                $options[] = $cartOption;
            } catch (Exception $ex) {
                flash()->warning($ex->getMessage());
            }
        }

        return $options;
    }
}
