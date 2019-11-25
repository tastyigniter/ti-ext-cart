<?php namespace Igniter\Cart\Components;

use Admin\Models\Orders_model;
use Auth;

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
            'orderPage' => [
                'label' => 'Account Order Page',
                'type' => 'string',
                'default' => 'account/order',
            ],
            'orderDateTimeFormat' => [
                'label' => 'Date time format to display the order date time',
                'type' => 'text',
                'default' => 'DD MMM \a\t HH:mm',
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