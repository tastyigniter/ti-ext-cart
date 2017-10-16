<?php namespace SamPoyigi\Cart\Components;

use SamPoyigi\Cart\Models\Settings_model;
use Igniter\Core\App;
use Igniter\Models\Image_tool_model;

//use Illuminate\Support\Facades\App;

class Cart extends \System\Classes\BaseComponent
{
//	protected $referrer_uri;
//
//	public function __construct($controller = null, $params = []) {
//		parent::__construct($controller, $params);                                                                    // calls the constructor
//
//		$this->load->model('cart/Cart_model');                                                        // load the cart model
//		$this->load->model('Image_tool_model');                                                        // load the Image tool model
//
//		$this->load->library('user_agent');
//		$this->load->library('location');
//		$this->location->initialize();
//
//		$this->load->library('cart');                                                            // load the cart library
//		$this->load->library('currency');                                                        // load the currency library
//
//		$this->lang->load('cart/cart');
//
//		$this->load->library('cart/Cart_lib');                                                // load the cart module library
//
//		$referrer_uri = explode('/', str_replace(site_url(), '', $this->agent->referrer()));
//		$this->referrer_uri = ($this->uri->rsegment(1) === 'cart' AND !empty($referrer_uri[0])) ? $referrer_uri[0] : $this->uri->rsegment(1);
//	}

    /**
     * @var \Sampoyigi\Cart\Classes\Cart
     */
    public $cart;

    public function onRun()
    {
        $this->cart = app()->make('cart');

        $this->addCss('cart/assets/stylesheet.css', 'cart-module-css');
        $this->addJs('cart/assets/cartbox.js', 'cart-box-js');

        $this->showCartImage = Settings_model::get('show_cart_images');
        $this->cartImageWidth = Settings_model::get('cart_images_h');
        $this->cartImageHeight = Settings_model::get('cart_images_w');
//        $this->hasSearchQuery = $this->location->hasSearchQuery();

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['showCartImage'] = $this->showCartImage;
        $this->page['cartImageWidth'] = $this->cartImageWidth;
        $this->page['cartImageHeight'] = $this->cartImageHeight;
        $this->page['hasSearchQuery'] = $this->hasSearchQuery;
        $this->page['showPaymentButton'] = ($this->controller->getClass() == 'checkout' AND $this->controller->getAction() != 'success');

        $this->page['addEventHandler'] = $this->getEventHandler('onLoadAddItemPopup');
        $this->page['updateEventHandler'] = $this->getEventHandler('onUpdateCart');
        $this->page['removeEventHandler'] = $this->getEventHandler('onRemoveItem');
        $this->page['changeEventHandler'] = $this->getEventHandler('onChangeOrderType');

//        $this->cart->add(33, 'Puff', 1, 5);
        $this->page['appliedCouponCode'] = ''; //$this->cart->modifier('coupon');

        $this->page['cart'] = $this->cart;
        $this->page['cartAlias'] = $this->cart->currentInstance();
        $this->page['cartItems'] = $this->cart->content();
        $this->page['cartTotals'] = $this->listTotals();
        $this->page['countCartItems'] = $this->cart->count();

//        $this->page['isClosed'] = $this->location->isClosed();
//        $this->page['orderType'] = $this->location->orderType();
//        $this->page['canAcceptOrder'] = $this->location->checkOrderType();
//
//        $this->page['hasDelivery'] = $this->location->hasDelivery();
//        $this->page['hasCollection'] = $this->location->hasCollection();
//        $this->page['deliveryStatus'] = $this->location->workingStatus('delivery');
//        $this->page['collectionStatus'] = $this->location->workingStatus('collection');
//        $this->page['deliveryTime'] = $this->location->deliveryTime();
//        $this->page['collectionTime'] = $this->location->collectionTime();
//
//        if ($this->page['deliveryStatus'] == 'opening')
//            $this->page['deliveryTime'] = $this->location->workingTime('delivery', 'open');
//
//        if ($this->page['collectionStatus'] == 'opening')
//            $this->page['collectionTime'] = $this->location->workingTime('collection', 'open');
    }

    public function onLoadAddItemPopup()
    {

    }

    public function onUpdateCart()
    {

    }

    public function onRemoveItem()
    {

    }

    public function onChangeOrderType()
    {

    }

//    public function index() {
//        $data = $this->getCart();
//        $this->load->view('cart/cart', $data);
//    }

    public function add()
    {                                                                        // add() method to add item to cart
        $json = [];

        if (!$this->input->is_ajax_request()) {

            $json['error'] = lang('alert_bad_request');
        }
        else if ($this->location->orderType() == '1' AND $this->config->item('location_order') == '1' AND !$this->location->hasSearchQuery()) {                                                        // if local restaurant is not selected

            $json['error'] = lang('alert_no_search_query');
        }
        else if (($response = $this->cart_lib->validateOrderType('', FALSE)) !== TRUE) {

            $json['error'] = $response;
        }
        else if (!$this->input->post('menu_id')) {

            $json['error'] = lang('alert_no_menu_selected');
        }
        else if ($menu_data = $this->Cart_model->getMenu($this->input->post('menu_id'))) {

            $quantity = (is_numeric($this->input->post('quantity'))) ? $this->input->post('quantity') : 0;

            $alert_msg = $this->cart_lib->validateCartMenu($menu_data, ['qty' => $quantity]);
            if (!empty($alert_msg) AND is_string($alert_msg)) {
                $json['error'] = $alert_msg;
            }

            $menu_options = $this->Cart_model->getMenuOptions($menu_data['menu_id']);                        // get menu option data based on menu option id from getMenuOption method in Menus model

            $cart_options = $this->cart_lib->validateCartMenuOption($menu_data, $menu_options);
            if (!empty($cart_options) AND is_string($cart_options)) {
                $json['option_error'] = $cart_options;
                $cart_options = [];
            }

            if ($cart_item = $this->cart->get_item($this->input->post('row_id'))) {
                $quantity = ($quantity <= 0) ? $cart_item['qty'] + $quantity : $quantity;
            }

            $price = (!empty($menu_data['special_status']) AND $menu_data['is_special'] == '1') ? $menu_data['special_price'] : $menu_data['menu_price'];

            $cart_data = [                                                                // create an array of item to be added to cart with id, name, qty, price and options as keys
                'rowid'   => !empty($cart_item['rowid']) ? $cart_item['rowid'] : null,
                'id'      => $menu_data['menu_id'],
                'name'    => $menu_data['menu_name'],
                'qty'     => $quantity,
                'price'   => $price,
                'comment' => $this->input->post('comment') ? substr(htmlspecialchars(trim($this->input->post('comment'))), 0, 50) : '',
                'options' => $cart_options,
            ];
        }

        if (!$json AND !empty($cart_data)) {
            if ($cart_data['rowid'] !== null AND $this->cart->update($cart_data)) {
                $json['success'] = lang('alert_menu_updated');                    // display success message
            }
            else if ($this->cart->insert($cart_data)) {
                $json['success'] = lang('alert_menu_added');                    // display success message
            }

            if (!isset($json['success'])) {
                $json['error'] = lang('alert_unknown_error');                            // display error message
            }
        }

        $this->output->set_output(json_encode($json));                                            // encode the json array and set final out to be sent to jQuery AJAX
    }

    public function options()
    {                                                                    // _updateModule() method to update cart
        $menu_data = $this->Cart_model->getMenu($this->input->get('menu_id'));

        if ($cart_item = $this->cart->get_item($this->input->get('row_id'))) {
            $data['text_heading'] = lang('text_update_heading');
            $quantity = $cart_item['qty'];
        }
        else {
            $data['text_heading'] = lang('text_add_heading');
        }

        $data['menu_id'] = $this->input->get('menu_id');
        $data['row_id'] = $this->input->get('row_id');
        $data['menu_name'] = $menu_data['menu_name'];
        $data['menu_price'] = $this->currency->format($menu_data['menu_price']);
        $data['description'] = $menu_data['menu_description'];
        $data['quantities'] = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
        $data['quantity'] = (isset($quantity)) ? $quantity : 1;
        $data['comment'] = isset($cart_item['comment']) ? $cart_item['comment'] : '';

        $menu_photo = (!empty($menu_data['menu_photo'])) ? $menu_data['menu_photo'] : 'data/no_photo.png';
        $menu_images_w = (is_numeric($this->config->item('menu_images_w'))) ? $this->config->item('menu_images_w') : '154';
        $menu_images_h = (is_numeric($this->config->item('menu_images_h'))) ? $this->config->item('menu_images_h') : '154';
        $data['menu_image'] = Image_tool_model::resize($menu_photo, $menu_images_w, $menu_images_h);

        $data['cart_option_value_ids'] = (!empty($cart_item['options'])) ?
            $this->cart->product_options_ids($this->input->get('row_id')) : [];

        // get menu option data based on menu option id from getMenuOption method in Menus model
        $data['menu_options'] = [];
        if ($menu_options = $this->Cart_model->getMenuOptions($this->input->get('menu_id'))) {
            foreach ($menu_options as $menu_id => $option) {
                $option_values_data = [];

                $option_values = $this->Cart_model->getMenuOptionValues($option['menu_option_id'], $option['option_id']);
                foreach ($option_values as $value) {
                    $option_values_data[] = [
                        'option_value_id'      => $value['option_value_id'],
                        'menu_option_value_id' => $value['menu_option_value_id'],
                        'value'                => $value['value'],
                        'price'                => (empty($value['new_price']) OR $value['new_price'] == '0.00') ? $this->currency->format($value['price']) : $this->currency->format($value['new_price']),
                    ];
                }

                $data['menu_options'][$option['menu_option_id']] = [
                    'menu_option_id'   => $option['menu_option_id'],
                    'menu_id'          => $option['menu_id'],
                    'option_id'        => $option['option_id'],
                    'option_name'      => $option['option_name'],
                    'display_type'     => $option['display_type'],
                    'priority'         => $option['priority'],
                    'default_value_id' => isset($option['default_value_id']) ? $option['default_value_id'] : 0,
                    'option_values'    => $option_values_data,
                ];
            }
        }

        $data['cart_option_alert'] = $this->alert->display('cart_option_alert');

        $this->load->view('cart/cart_options', $data);
    }

    public function order_type()
    {                                                                // _updateModule() method to update cart
        $json = [];

        $order_type = (is_numeric($this->input->post('order_type'))) ? $this->input->post('order_type') : null;

        if (!$json AND $order_type) {
            $response = $this->cart_lib->validateOrderType($order_type);

            if ($response !== TRUE) {
                $json['error'] = $response;
            }

            $this->location->setOrderType($order_type);

            $json['order_type'] = $this->location->orderType();
            $json['redirect'] = referrer_url();
        }

        $this->output->set_output(json_encode($json));    // encode the json array and set final out to be sent to jQuery AJAX
    }

    public function coupon()
    {                                                                    // _updateModule() method to update cart
        $json = [];

        if (!$json AND $this->cart->contents() AND is_string($this->input->post('code'))) {
            switch ($this->input->post('action')) {
                case 'remove':
                    $this->cart->remove_coupon($this->input->post('code'));
                    $json['success'] = lang('alert_coupon_removed');                        // display success message
                    break;

                case 'add':
                    if (($response = $this->cart_lib->validateCoupon($this->input->post('code'))) === TRUE) {
                        $json['success'] = lang('alert_coupon_applied');                        // display success message
                    }
                    else {
                        $json['error'] = $response;
                    }
                    break;
                default:
                    $json['redirect'] = referrer_url();
                    break;
            }
        }

        $this->output->set_output(json_encode($json));                                            // encode the json array and set final out to be sent to jQuery AJAX
    }

    public function remove()
    {                                                                    // remove() method to update cart
        $json = [];

        if (!$json) {
            if ($this->cart->update(['rowid' => $this->input->post('row_id'), 'qty' => $this->input->post('quantity')])) {                                            // pass the cart_data array to add item to cart, if successful
                $json['success'] = lang('alert_menu_updated');                        // display success message
            }
            else {                                                                            // else redirect to menus page
                $json['redirect'] = site_url(referrer_url());
            }
        }

        $this->output->set_output(json_encode($json));    // encode the json array and set final out to be sent to jQuery AJAX
    }

    public function getCart($is_mobile = FALSE)
    {
        $data['rsegment'] = $rsegment = $this->referrer_uri;

        $this->addCss(extension_url('cart/assets/stylesheet.css'), 'cart-module-css');

        $data['is_opened'] = $this->location->isOpened();
        $data['order_type'] = $this->location->orderType();
        $data['search_query'] = $this->location->searchQuery();
        $data['opening_status'] = $this->location->workingStatus('opening');
        $data['delivery_status'] = $this->location->workingStatus('delivery');
        $data['collection_status'] = $this->location->workingStatus('collection');
        $data['has_delivery'] = $this->location->hasDelivery();
        $data['has_collection'] = $this->location->hasCollection();

        $data['show_cart_images'] = $this->setting('show_cart_images');
        $data['cart_images_h'] = $this->setting('cart_images_h');
        $data['cart_images_w'] = $this->setting('cart_images_w');

        $data['delivery_time'] = $this->location->deliveryTime();
        if ($data['delivery_status'] === 'closed') {
            $data['delivery_time'] = 'closed';
        }
        else if ($data['delivery_status'] === 'opening') {
            $data['delivery_time'] = $this->location->workingTime('delivery', 'open');
        }

        $data['collection_time'] = $this->location->collectionTime();
        if ($data['collection_status'] === 'closed') {
            $data['collection_time'] = 'closed';
        }
        else if ($data['collection_status'] === 'opening') {
            $data['collection_time'] = $this->location->workingTime('collection', 'open');
        }

        $order_data = $this->session->userdata('order_data');
        if ($this->input->post('checkout_step')) {
            $checkout_step = $this->input->post('checkout_step');
        }
        else if (isset($order_data['checkout_step'])) {
            $checkout_step = $order_data['checkout_step'];
        }
        else {
            $checkout_step = 'one';
        }

        if ($rsegment === 'checkout' AND $checkout_step === 'two') {
            $data['button_order'] = '<a class="btn btn-order btn-primary btn-block btn-lg" onclick="$(\'#checkout-form\').submit();">'.lang('button_confirm').'</a>';
        }
        else if ($rsegment == 'checkout') {
            $data['button_order'] = '<a class="btn btn-order btn-primary btn-block btn-lg" onclick="$(\'#checkout-form\').submit();">'.lang('button_payment').'</a>';
        }
        else {
            $data['button_order'] = '<a class="btn btn-order btn-primary btn-block btn-lg" href="'.site_url('checkout').'">'.lang('button_order').'</a>';
        }

        if ($this->location->isClosed() OR !$this->location->checkOrderType()) {
            $data['button_order'] = '<a class="btn btn-default btn-block btn-lg" href="'.site_url('checkout').'"><b>'.lang('cart.text_is_closed').'</b></a>';
        }

        $menus = $this->Cart_model->getMenus();

        $data['cart_items'] = $data['cart_totals'] = [];
        if ($cart_contents = $this->cart->contents()) {                                                            // checks if cart contents is not empty
            foreach ($cart_contents as $row_id => $cart_item) {                                // loop through items in cart
                $menu_data = isset($menus[$cart_item['id']]) ? $menus[$cart_item['id']] : FALSE;                // get menu data based on cart item id from getMenu method in Menus model

                if (($alert_msg = $this->cart_lib->validateCartMenu($menu_data, $cart_item)) === TRUE) {
                    $cart_image = '';
                    if (isset($data['show_cart_images']) AND $data['show_cart_images'] == '1') {
                        $menu_photo = (!empty($menu_data['menu_photo'])) ? $menu_data['menu_photo'] : 'data/no_photo.png';
                        $cart_image = Image_tool_model::resize($menu_photo, $data['cart_images_h'], $data['cart_images_w']);
                    }

                    // load menu data into array
                    $data['cart_items'][] = [
                        'rowid'     => $cart_item['rowid'],
                        'menu_id'   => $cart_item['id'],
                        'name'      => (strlen($cart_item['name']) > 25) ? strtolower(substr($cart_item['name'], 0, 25)).'...' : strtolower($cart_item['name']),
                        //add currency symbol and format item price to two decimal places
                        'price'     => $this->currency->format($cart_item['price']),
                        'qty'       => $cart_item['qty'],
                        'image'     => $cart_image,
                        //add currency symbol and format item subtotal to two decimal places
                        'sub_total' => $this->currency->format($cart_item['subtotal']),
                        'comment'   => isset($cart_item['comment']) ? $cart_item['comment'] : '',
                        'options'   => ($this->cart->has_options($row_id) == TRUE) ? $this->cart->product_options_string($row_id) : '',
                    ];
                }
                else {
                    $this->alert->set('custom_now', $alert_msg, 'cart');
                    $this->cart->update(['rowid' => $cart_item['rowid'], 'qty' => '0']);                                        // pass the cart_data array to add item to cart, if successful
                }
            }

            if (($response = $this->cart_lib->validateOrderType()) !== TRUE) {
                $this->alert->set('custom', $response, 'cart');
            }

            if (($response = $this->cart_lib->validateDeliveryCharge($this->cart->total())) !== TRUE) {
                $this->alert->set('custom', $response, 'cart');
            }

            if (($response = $this->cart_lib->validateCoupon($this->cart->coupon_code())) !== TRUE) {
                $this->alert->set('custom', $response, 'cart');
            }

            Events::trigger('cart_before_cart_totals');

            $this->cart->calculate_tax();

            $data['cart_totals'] = $this->cart_lib->cartTotals();
        }

        $data['is_checkout'] = ($rsegment === 'checkout') ? TRUE : FALSE;
        $data['is_mobile'] = $is_mobile;

        $data['cart_alert'] = $this->alert->display('cart');

        return $data;
    }

    protected function listTotals()
    {
        return [
            'order_total' => (object)[
                'code'      => 'order_total',
                'valueFrom' => 'total',
                'label'     => 'Order Total',
            ],
        ];
    }
}
