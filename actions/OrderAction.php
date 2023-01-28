<?php

namespace Igniter\Cart\Actions;

use System\Actions\ModelAction;

class OrderAction extends ModelAction
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;

        $this->model->fillable(['customer_id', 'address_id', 'first_name', 'last_name', 'email', 'telephone', 'comment', 'payment']);
    }

    public function getUrl($page, $params = [])
    {
        $defaults = [
            'id' => $this->model->getKey(),
            'hash' => $this->model->hash,
        ];

        $params = !is_null($params)
            ? array_merge($defaults, $params)
            : [];

        return page_url($page, $params);
    }
}
