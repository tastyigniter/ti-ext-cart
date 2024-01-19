<?php

namespace Igniter\Cart\Actions;

use Igniter\System\Actions\ModelAction;

class OrderAction extends ModelAction
{
    public function __construct($model)
    {
        parent::__construct($model);

        $this->model->fillable(['customer_id', 'address_id', 'first_name', 'last_name', 'email', 'telephone', 'comment', 'delivery_comment', 'payment']);
    }
}
