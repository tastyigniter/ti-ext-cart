<?php

namespace Igniter\Cart\Components;

use Admin\Models\Orders_model;
use Main\Facades\Auth;
use Main\Traits\UsesPage;

class Orders extends \System\Classes\BaseComponent
{
    use UsesPage;

    public function defineProperties()
    {
        return [
            'itemsPerPage' => [
                'label' => 'Items Per Page',
                'type' => 'number',
                'default' => 20,
                'validationRule' => 'required|integer',
            ],
            'sortOrder' => [
                'label' => 'Sort order',
                'type' => 'text',
                'default' => 'created_at desc',
                'validationRule' => 'required|string',
            ],
            'orderPage' => [
                'label' => 'Account Order Page',
                'type' => 'select',
                'default' => 'account'.DIRECTORY_SEPARATOR.'order',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['orderDateTimeFormat'] = lang('system::lang.moment.date_time_format_short');
        $this->page['orderPage'] = $this->property('orderPage');
        $this->page['customerOrders'] = $this->loadOrders();
    }

    protected function loadOrders()
    {
        if (!$customer = Auth::customer())
            return [];

        return Orders_model::with(['location', 'status'])
            ->whereProcessed(true)
            ->listFrontEnd([
                'page' => $this->param('page'),
                'pageLimit' => $this->property('itemsPerPage'),
                'sort' => $this->property('sortOrder', 'created_at desc'),
                'customer' => $customer,
            ]);
    }
}
