<?php namespace SamPoyigi\Cart\Models;

use Admin\Models\Orders_model as BaseOrders_model;
use Main\Classes\MainController;

class Orders_model extends BaseOrders_model
{
    protected static $receiptPageName;

    protected $fillable = ['customer_id', 'first_name', 'last_name', 'email', 'telephone', 'comment'];

    public function setReceiptPageName($pageName)
    {
        self::$receiptPageName = $pageName;
    }

    public function getReceiptUrl()
    {
        $controller = MainController::getController() ?: new MainController;

        $pageName = self::$receiptPageName;
        if ($this->return_page)
            $pageName = $this->return_page;

        return $controller->pageUrl($pageName, $this->getUrlParams());
    }

    public function getUrlParams()
    {
        return [
            'order_id' => $this->order_id,
            'hash'     => $this->hash,
        ];
    }

    public function listCustomerAddresses()
    {
        return [];
    }
}