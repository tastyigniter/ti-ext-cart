<?php

namespace Igniter\Cart\Components;

use Admin\Models\Orders_model;
use Auth;
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
                'default' => 'date_added desc',
                'validationRule' => 'required|string',
            ],
            'orderPage' => [
                'label' => 'Account Order Page',
                'type' => 'select',
                'default' => 'account/order',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'orderDateTimeFormat' => [
                'label' => 'Date time format to display the order date time',
                'type' => 'text',
                'default' => 'DD MMM \a\t HH:mm',
                'validationRule' => 'required|string',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['orderDateTimeFormat'] = $this->property('orderDateTimeFormat');
        $this->page['orderPage'] = $this->property('orderPage');
        $this->page['customerOrders'] = $this->loadOrders();
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
