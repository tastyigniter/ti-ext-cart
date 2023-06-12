<?php

namespace Igniter\Cart\Models;

use Igniter\Cart\Models\Order as BaseOrder;
use Igniter\Main\Classes\MainController;

class Ordere extends BaseOrder
{
    protected $fillable = ['customer_id', 'address_id', 'first_name', 'last_name', 'email', 'telephone', 'comment', 'payment'];

    public function getUrl($page, $params = [])
    {
        $defaults = [
            'id' => $this->getKey(),
            'hash' => $this->hash,
        ];

        $params = !is_null($params)
            ? array_merge($defaults, $params)
            : [];

        $controller = MainController::getController() ?: new MainController;

        return $controller->pageUrl($page, $params);
    }

    public function getMorphClass()
    {
        return 'orders';
    }
}
