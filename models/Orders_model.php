<?php

namespace Igniter\Cart\Models;

use Admin\Models\Orders_model as BaseOrders_model;
use Main\Classes\MainController;

/**
 * Orders Model Class
 * @deprecated remove before v4. Added for backward compatibility, use Admin\Models\Orders_model
 */
class Orders_model extends BaseOrders_model
{
    protected $fillable = ['customer_id', 'address_id', 'first_name', 'last_name', 'email', 'telephone', 'comment', 'delivery_comment', 'payment'];

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
