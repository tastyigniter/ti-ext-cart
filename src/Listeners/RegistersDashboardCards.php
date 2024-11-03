<?php

namespace Igniter\Cart\Listeners;

use Igniter\Cart\Models\Order;
use Igniter\System\Models\Settings;

class RegistersDashboardCards
{
    public function __invoke()
    {
        return [
            'sale' => [
                'label' => 'lang:igniter::admin.dashboard.text_total_sale',
                'icon' => ' text-success fa fa-4x fa-line-chart',
                'valueFrom' => [$this, 'getValue'],
            ],
            'lost_sale' => [
                'label' => 'lang:igniter::admin.dashboard.text_total_lost_sale',
                'icon' => ' text-danger fa fa-4x fa-line-chart',
                'valueFrom' => [$this, 'getValue'],
            ],
            'cash_payment' => [
                'label' => 'lang:igniter::admin.dashboard.text_total_cash_payment',
                'icon' => ' text-warning fa fa-4x fa-money-bill',
                'valueFrom' => [$this, 'getValue'],
            ],
            'order' => [
                'label' => 'lang:igniter::admin.dashboard.text_total_order',
                'icon' => ' text-success fa fa-4x fa-shopping-cart',
                'valueFrom' => [$this, 'getValue'],
            ],
            'delivery_order' => [
                'label' => 'lang:igniter::admin.dashboard.text_total_delivery_order',
                'icon' => ' text-primary fa fa-4x fa-truck',
                'valueFrom' => [$this, 'getValue'],
            ],
            'collection_order' => [
                'label' => 'lang:igniter::admin.dashboard.text_total_collection_order',
                'icon' => ' text-info fa fa-4x fa-shopping-bag',
                'valueFrom' => [$this, 'getValue'],
            ],
            'completed_order' => [
                'label' => 'lang:igniter::admin.dashboard.text_total_completed_order',
                'icon' => ' text-success fa fa-4x fa-receipt',
                'valueFrom' => [$this, 'getValue'],
            ],
        ];
    }

    public function getValue($code, $start, $end, callable $callback)
    {
        return match ($code) {
            'sale' => $this->getTotalSaleSum($callback),
            'lost_sale' => $this->getTotalLostSaleSum($callback),
            'cash_payment' => $this->getTotalCashPaymentSum($callback),
            'order' => $this->getTotalOrderSum($callback),
            'delivery_order' => $this->getTotalDeliveryOrderSum($callback),
            'collection_order' => $this->getTotalCollectionOrderSum($callback),
            'completed_order' => $this->getTotalCompletedOrderSum($callback),
            default => 0,
        };
    }

    /**
     * Return the total amount of order sales
     */
    protected function getTotalSaleSum(callable $callback): string
    {
        $query = Order::query();
        $query->where('status_id', '>', '0')
            ->where('status_id', '!=', Settings::get('canceled_order_status'));

        $callback($query);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total amount of lost order sales
     */
    protected function getTotalLostSaleSum(callable $callback): string
    {
        $query = Order::query();
        $query->where(function($query) {
            $query->where('status_id', '<=', '0');
            $query->orWhere('status_id', Settings::get('canceled_order_status'));
        });

        $callback($query);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total amount of cash payment order sales
     */
    protected function getTotalCashPaymentSum(callable $callback): string
    {
        $query = Order::query();
        $query->where(function($query) {
            $query->where('status_id', '>', '0');
            $query->where('status_id', '!=', Settings::get('canceled_order_status'));
        })->where('payment', 'cod');

        $callback($query);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total number of orders placed
     */
    protected function getTotalOrderSum(callable $callback): int
    {
        $query = Order::query();

        $callback($query);

        return $query->count();
    }

    /**
     * Return the total number of completed orders
     */
    protected function getTotalCompletedOrderSum(callable $callback): int
    {
        $query = Order::query();
        $query->whereIn('status_id', Settings::get('completed_order_status') ?? []);

        $callback($query);

        return $query->count();
    }

    /**
     * Return the total number of delivery orders
     */
    protected function getTotalDeliveryOrderSum(callable $callback): string
    {
        $query = Order::query();
        $query->whereIn('status_id', Settings::get('completed_order_status') ?? [])
            ->where(function($query) {
                $query->where('order_type', '1');
                $query->orWhere('order_type', 'delivery');
            });

        $callback($query);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total number of collection orders
     */
    protected function getTotalCollectionOrderSum(callable $callback): string
    {
        $query = Order::query();
        $query->whereIn('status_id', Settings::get('completed_order_status') ?? [])
            ->where(function($query) {
                $query->where('order_type', '2');
                $query->orWhere('order_type', 'collection');
            });

        $callback($query);

        return currency_format($query->sum('order_total') ?? 0);
    }
}
