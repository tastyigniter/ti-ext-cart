<?php namespace SamPoyigi\Cart\Controllers;

class Cart extends \Igniter\Core\MainController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->lang->load('cart');

//        $this->addComponent('cart', 'fullCart')->onRun();

//		$this->load->module('cart');
//        $data = $this->cart->getCart(TRUE);

        $this->template->setTitle($this->lang->line('text_heading'));
        $this->addCss(extension_url('cart/assets/stylesheet.css'), 'cart-module-css');

        $this->template->render('cart', []);
    }
}