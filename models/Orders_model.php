<?php namespace SamPoyigi\Cart\Models;

use Admin\Models\Orders_model as BaseOrders_model;
use Admin\Models\Statuses_model;
use Main\Classes\MainController;

class Orders_model extends BaseOrders_model
{
    protected static $receiptPageName;

    protected static $orderViewPageName;

    protected $fillable = ['customer_id', 'first_name', 'last_name', 'email', 'telephone', 'comment', 'payment'];

    public function setReceiptPageName($pageName)
    {
        self::$receiptPageName = $pageName;
    }

    public function setOrderViewPageName($pageName)
    {
        self::$orderViewPageName = $pageName;
    }

    public function getReceiptUrl()
    {
        $controller = MainController::getController() ?: new MainController;

        $pageName = self::$receiptPageName;
        if ($this->return_page)
            $pageName = $this->return_page;

        return $controller->pageUrl($pageName, $this->getUrlParams());
    }

    public function getOrderViewPageUrl()
    {
        $controller = MainController::getController() ?: new MainController;

        $pageName = self::$orderViewPageName;

        return $controller->pageUrl($pageName, $this->getUrlParams());
    }

    public function getUrlParams()
    {
        return [
            'orderId' => $this->order_id,
            'hash'    => $this->hash,
        ];
    }

    public function listCustomerAddresses()
    {
        return [];
    }

    /**
     * Complete order by sending email confirmation and,
     * updating order status
     *
     * @param $status
     *
     * @return bool
     */
    public function completeOrder($status)
    {
        if (!$status instanceof Statuses_model)
            return FALSE;

        $this->status_id = $status->getKey();
        $this->save();

        $this->mailSend('sampoyigi.cart::mail.order', 'customer');
        $this->mailSend('sampoyigi.cart::mail.order_alert', 'location');
        $this->mailSend('sampoyigi.cart::mail.order_alert', 'admin');

        $this->addStatusHistory(['notify' => 1]);

        // @todo: fire order.completed event
    }

    public function mailGetData()
    {
        return array_merge(parent::mailGetData(), [
            'order_view_url' => $this->getOrderViewPageUrl()
        ]);
    }
}