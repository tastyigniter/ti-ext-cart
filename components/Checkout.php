<?php

namespace Igniter\Cart\Components;

use Admin\Traits\ValidatesForm;
use Auth;
use Exception;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Classes\OrderManager;
use Igniter\Flame\Exception\ApplicationException;
use Illuminate\Http\RedirectResponse;
use Location;
use Redirect;
use System\Classes\BaseComponent;

class Checkout extends BaseComponent
{
    use ValidatesForm;
    use \Main\Traits\UsesPage;

    /**
     * @var \Igniter\Cart\Classes\CartManager
     */
    protected $cartManager;

    /**
     * @var \Igniter\Cart\Classes\OrderManager
     */
    protected $orderManager;

    /**
     * @var  \Igniter\Cart\Models\Orders_model
     */
    protected $order;

    public function initialize()
    {
        $this->orderManager = OrderManager::instance();
        $this->cartManager = CartManager::instance();
    }

    public function defineProperties()
    {
        return [
            'showCountryField' => [
                'label' => 'Whether to display the country form field',
                'type' => 'switch',
                'default' => FALSE,
            ],
            'agreeTermsPage' => [
                'label' => 'lang:igniter.cart::default.checkout.label_checkout_terms',
                'type' => 'select',
                'options' => [static::class, 'getStaticPageOptions'],
                'comment' => 'lang:igniter.cart::default.checkout.help_checkout_terms',
            ],
            'menusPage' => [
                'label' => 'lang:igniter.cart::default.checkout.label_checkout_terms',
                'type' => 'select',
                'default' => 'local/menus',
                'options' => [static::class, 'getThemePageOptions'],
                'comment' => 'Page to redirect to when checkout can not be performed.',
            ],
            'redirectPage' => [
                'label' => 'Page to redirect to when checkout fails',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'checkout/checkout',
            ],
            'successPage' => [
                'label' => 'Page to redirect to when checkout is successful',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'checkout/success',
            ],
            'cartBoxAlias' => [
                'label' => 'Specify the CartBox component alias used to refresh the cart after a payment is selected',
                'type' => 'text',
                'default' => 'cartBox',
            ],
        ];
    }

    public function onRun()
    {
        if ($redirect = $this->isOrderMarkedAsProcessed())
            return $redirect;

        if ($this->checkCheckoutSecurity())
            return Redirect::to(restaurant_url($this->property('menusPage')));

        $this->prepareVars();
    }

    public function onRender()
    {
        foreach ($this->getPaymentGateways() as $paymentGateway) {
            $paymentGateway->beforeRenderPaymentForm($paymentGateway, $this->controller);
        }

        $this->addJs('js/checkout.js', 'checkout-js');
    }

    protected function prepareVars()
    {
        $this->page['showCountryField'] = (bool)$this->property('showCountryField', 1);
        $this->page['agreeTermsSlug'] = $this->getAgreeTermsPageSlug();
        $this->page['redirectPage'] = $this->property('redirectPage');
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['successPage'] = $this->property('successPage');

        $this->page['choosePaymentEventHandler'] = $this->getEventHandler('onChoosePayment');
        $this->page['deletePaymentEventHandler'] = $this->getEventHandler('onDeletePaymentProfile');
        $this->page['confirmCheckoutEventHandler'] = $this->getEventHandler('onConfirm');

        $this->page['order'] = $this->getOrder();
        $this->page['paymentGateways'] = $this->getPaymentGateways();
    }

    public function fetchPartials()
    {
        $this->prepareVars();

        return [
            '[data-partial="checkoutPayments"]' => $this->renderPartial('@payments'),
        ];
    }

    public function getOrder()
    {
        if (!is_null($this->order))
            return $this->order;

        $order = $this->orderManager->loadOrder();

        if (!$order->isPaymentProcessed())
            $this->orderManager->applyRequiredAttributes($order);

        return $this->order = $order;
    }

    public function getPaymentGateways()
    {
        $order = $this->getOrder();

        return $order->order_total > 0
            ? $this->orderManager->getPaymentGateways() : [];
    }

    public function onChoosePayment()
    {
        $paymentCode = post('code');

        if (!$payment = $this->orderManager->getPayment($paymentCode))
            throw new ApplicationException(lang('igniter.cart::default.checkout.error_invalid_payment'));

        $this->orderManager->applyCurrentPaymentFee($payment->code);

        $this->controller->pageCycle();

        $result = $this->fetchPartials();

        if ($cartBox = $this->controller->findComponentByAlias($this->property('cartBoxAlias'))) {
            $result = array_merge($result, $cartBox->fetchPartials());
        }

        return $result;
    }

    public function onConfirm()
    {
        if ($redirect = $this->isOrderMarkedAsProcessed())
            return $redirect;

        $data = post();
        $data['cancelPage'] = $this->property('redirectPage');
        $data['successPage'] = $this->property('successPage');

        $data = $this->processDeliveryAddress($data);

        $this->validateCheckoutSecurity();

        try {
            $this->validate($data, $this->createRules());

            $order = $this->getOrder();

            if ($order->isDeliveryType()) {
                $this->orderManager->validateDeliveryAddress(array_get($data, 'address', []));
            }

            $this->orderManager->saveOrder($order, $data);

            if (($redirect = $this->orderManager->processPayment($order, $data)) === FALSE)
                return;

            if ($redirect instanceof RedirectResponse)
                return $redirect;

            if ($redirect = $this->isOrderMarkedAsProcessed())
                return $redirect;
        }
        catch (Exception $ex) {
            flash()->warning($ex->getMessage())->important();

            return Redirect::back()->withInput();
        }
    }

    public function onDeletePaymentProfile()
    {
        $customer = Auth::customer();
        $payment = $this->orderManager->getPayment(post('code'));

        if (!$payment OR !$payment->paymentProfileExists($customer))
            throw new ApplicationException(lang('igniter.cart::default.checkout.error_invalid_payment'));

        $payment->deletePaymentProfile($customer);

        $this->controller->pageCycle();

        $result = $this->fetchPartials();

        if ($cartBox = $this->controller->findComponentByAlias($this->property('cartBoxAlias'))) {
            $result = array_merge($result, $cartBox->fetchPartials());
        }

        return $result;
    }

    protected function checkCheckoutSecurity()
    {
        try {
            $this->validateCheckoutSecurity();

            if ($this->cartManager->cartTotalIsBelowMinimumOrder())
                return TRUE;
        }
        catch (Exception $ex) {
            flash()->warning($ex->getMessage())->now();

            return TRUE;
        }
    }

    protected function validateCheckoutSecurity()
    {
        $this->cartManager->validateContents();

        $this->orderManager->validateCustomer(Auth::getUser());

        $this->cartManager->validateLocation();

        $this->cartManager->validateOrderTime();
    }

    protected function createRules()
    {
        $namedRules = [
            ['first_name', 'lang:igniter.cart::default.checkout.label_first_name', 'required|between:1,48'],
            ['last_name', 'lang:igniter.cart::default.checkout.label_last_name', 'required|between:1,48'],
            ['email', 'lang:igniter.cart::default.checkout.label_email', 'sometimes|required|email:filter|max:96|unique:customers'],
            ['telephone', 'lang:igniter.cart::default.checkout.label_telephone', ''],
            ['comment', 'lang:igniter.cart::default.checkout.label_comment', 'max:500'],
            ['payment', 'lang:igniter.cart::default.checkout.label_payment_method', 'sometimes|required|alpha_dash'],
            ['terms_condition', 'lang:button_agree_terms', 'sometimes|integer'],
        ];

        if (Location::orderTypeIsDelivery()) {
            $namedRules[] = ['address_id', 'lang:igniter.cart::default.checkout.label_address', 'required|integer'];
            $namedRules[] = ['address.address_1', 'lang:igniter.cart::default.checkout.label_address_1', 'required|min:3|max:128'];
            $namedRules[] = ['address.city', 'lang:igniter.cart::default.checkout.label_city', 'min:2|max:128'];
            $namedRules[] = ['address.state', 'lang:igniter.cart::default.checkout.label_state', 'max:128'];
            $namedRules[] = ['address.postcode', 'lang:igniter.cart::default.checkout.label_postcode', 'string'];
            $namedRules[] = ['address.country_id', 'lang:igniter.cart::default.checkout.label_country', 'required|integer'];
        }

        return $namedRules;
    }

    protected function isCheckoutSuccessPage()
    {
        return $this->page->getBaseFileName() == $this->property('successPage');
    }

    protected function isOrderMarkedAsProcessed()
    {
        $order = $this->getOrder();
        if (!$order->isPaymentProcessed())
            return FALSE;

        $redirectUrl = $order->getUrl($this->property('successPage'));
        if ($this->isCheckoutSuccessPage())
            $redirectUrl = $this->controller->pageUrl($this->property('redirectPage'));

        return Redirect::to($redirectUrl);
    }

    protected function processDeliveryAddress($data)
    {
        $addressId = array_get($data, 'address_id');
        if ($address = $this->orderManager->findDeliveryAddress($addressId)) {
            $data['address'] = $address->toArray();
        }

        if (isset($data['address']) AND !isset($data['address']['country_id'])) {
            $data['address']['country_id'] = setting('country_id');
        }

        return $data;
    }

    protected function getAgreeTermsPageSlug()
    {
        return $this->getStaticPagePermalink($this->property('agreeTermsPage'));
    }
}