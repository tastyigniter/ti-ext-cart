<?php

namespace Igniter\Cart\Components;

use Admin\Traits\ValidatesForm;
use Exception;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Classes\OrderManager;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Facades\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redirect;
use Main\Facades\Auth;
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
     * @var  \Admin\Models\Orders_model
     */
    protected $order;

    public $checkoutStep;

    public function initialize()
    {
        $this->orderManager = OrderManager::instance();
        $this->cartManager = CartManager::instance();

        $this->checkoutStep = $this->param($this->property('stepParamName'), 'details');
    }

    public function defineProperties()
    {
        return [
            'isMultiStepCheckout' => [
                'label' => 'Whether to use a multi step checkout',
                'type' => 'switch',
                'default' => false,
                'validationRule' => 'required|boolean',
            ],
            'showAddress2Field' => [
                'label' => 'Whether to display the address 2 form field',
                'type' => 'switch',
                'default' => true,
                'validationRule' => 'required|boolean',
            ],
            'showCityField' => [
                'label' => 'Whether to display the city form field',
                'type' => 'switch',
                'default' => true,
                'validationRule' => 'required|boolean',
            ],
            'showStateField' => [
                'label' => 'Whether to display the state form field',
                'type' => 'switch',
                'default' => true,
                'validationRule' => 'required|boolean',
            ],
            'showPostcodeField' => [
                'label' => 'Whether to display the postcode form field',
                'type' => 'switch',
                'default' => false,
                'validationRule' => 'required|boolean',
            ],
            'showCountryField' => [
                'label' => 'Whether to display the country form field',
                'type' => 'switch',
                'default' => false,
                'validationRule' => 'required|boolean',
            ],
            'showCommentField' => [
                'label' => 'Whether to display the comment form field',
                'type' => 'switch',
                'default' => true,
                'validationRule' => 'required|boolean',
            ],
            'showDeliveryCommentField' => [
                'label' => 'Whether to display the delivery comment form field',
                'type' => 'switch',
                'default' => true,
                'validationRule' => 'required|boolean',
            ],
            'telephoneIsRequired' => [
                'label' => 'Whether the telephone field should be required',
                'type' => 'switch',
                'default' => false,
                'validationRule' => 'required|boolean',
            ],
            'agreeTermsPage' => [
                'label' => 'lang:igniter.cart::default.checkout.label_checkout_terms',
                'type' => 'select',
                'options' => [static::class, 'getStaticPageOptions'],
                'comment' => 'lang:igniter.cart::default.checkout.help_checkout_terms',
                'validationRule' => 'integer',
            ],
            'menusPage' => [
                'label' => 'Page to redirect to when checkout can not be performed',
                'type' => 'select',
                'default' => 'local'.DIRECTORY_SEPARATOR.'menus',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'redirectPage' => [
                'label' => 'Page to redirect to when checkout fails',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'checkout'.DIRECTORY_SEPARATOR.'checkout',
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'successPage' => [
                'label' => 'Page to redirect to when checkout is successful',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'checkout'.DIRECTORY_SEPARATOR.'success',
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'cartBoxAlias' => [
                'label' => 'Specify the CartBox component alias used to refresh the cart after a payment is selected',
                'type' => 'text',
                'default' => 'cartBox',
                'validationRule' => 'required|regex:/^[a-z0-9\-_]+$/i',
            ],
            'stepParamName' => [
                'type' => 'text',
                'default' => 'step',
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
        $this->page['isMultiStepCheckout'] = (bool)$this->property('isMultiStepCheckout', false);
        $this->page['showCountryField'] = (bool)$this->property('showCountryField', 1);
        $this->page['showPostcodeField'] = (bool)$this->property('showPostcodeField', 1);
        $this->page['showAddress2Field'] = (bool)$this->property('showAddress2Field', 1);
        $this->page['showCityField'] = (bool)$this->property('showCityField', 1);
        $this->page['showStateField'] = (bool)$this->property('showStateField', 1);
        $this->page['showCommentField'] = (bool)$this->property('showCommentField', 1);
        $this->page['showDeliveryCommentField'] = (bool)$this->property('showDeliveryCommentField', 1);
        $this->page['agreeTermsSlug'] = $this->getAgreeTermsPageSlug();
        $this->page['redirectPage'] = $this->property('redirectPage');
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['successPage'] = $this->property('successPage');

        $this->page['choosePaymentEventHandler'] = $this->getEventHandler('onChoosePayment');
        $this->page['deletePaymentEventHandler'] = $this->getEventHandler('onDeletePaymentProfile');
        $this->page['confirmCheckoutEventHandler'] = $this->getEventHandler('onConfirm');
        $this->page['validateCheckoutEventHandler'] = $this->getEventHandler('onValidate');

        $this->page['order'] = $order = $this->getOrder();
        $this->page['locationOrderType'] = resolve('location')->getOrderTypes()->get($order->order_type);
        $this->page['paymentGateways'] = $this->getPaymentGateways();
        $this->page['checkoutStep'] = $this->checkoutStep;
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

        return rescue(function () use ($data) {
            $order = $this->getOrder();

            $this->validateCheckout($data, $order);

            $this->orderManager->saveOrder($order, $data);

            if (!$this->canConfirmCheckout())
                return redirect()->to($this->currentPageUrl().'?step=pay');

            if (($redirect = $this->orderManager->processPayment($order, $data)) === false)
                return;

            if ($redirect instanceof RedirectResponse)
                return $redirect;

            if ($redirect = $this->isOrderMarkedAsProcessed())
                return $redirect;
        }, function (Exception $ex) {
            flash()->warning($ex->getMessage())->important();

            return Redirect::back()->withInput();
        });
    }

    public function onDeletePaymentProfile()
    {
        $customer = Auth::customer();
        $payment = $this->orderManager->getPayment(post('code'));

        if (!$payment || !$payment->paymentProfileExists($customer))
            throw new ApplicationException(lang('igniter.cart::default.checkout.error_invalid_payment'));

        $payment->deletePaymentProfile($customer);

        $this->controller->pageCycle();

        $result = $this->fetchPartials();

        if ($cartBox = $this->controller->findComponentByAlias($this->property('cartBoxAlias'))) {
            $result = array_merge($result, $cartBox->fetchPartials());
        }

        return $result;
    }

    public function onValidate()
    {
        $data = post();

        $this->validateCheckoutSecurity();

        $data = $this->processDeliveryAddress($data);

        $order = $this->getOrder();

        $this->validateCheckout($data, $order);

        $this->orderManager->saveOrder($order, $data);
    }

    protected function checkCheckoutSecurity()
    {
        try {
            $this->fireSystemEvent('igniter.cart.beforeCheckCheckoutSecurity', [$this]);

            $this->validateCheckoutSecurity();

            if ($this->cartManager->cartTotalIsBelowMinimumOrder())
                throw new ApplicationException(sprintf(lang('igniter.cart::default.alert_min_order_total'),
                    currency_format(resolve('location')->minimumOrderTotal())));

            if ($this->cartManager->deliveryChargeIsUnavailable())
                return true;
        } catch (Exception $ex) {
            flash()->warning($ex->getMessage())->now();

            return true;
        }
    }

    protected function validateCheckoutSecurity()
    {
        $this->cartManager->validateContents();

        $this->orderManager->validateCustomer(Auth::getUser());

        $this->cartManager->validateLocation();

        $this->cartManager->validateOrderTime();
    }

    protected function validateCheckout($data, $order)
    {
        $this->validate($data, $this->createRules(), [
            'email.unique' => lang('igniter.cart::default.checkout.error_email_exists'),
        ]);

        if ($this->checkoutStep === 'details' && $order->isDeliveryType()) {
            $this->orderManager->validateDeliveryAddress(array_get($data, 'address', []));
        }

        if ($this->canConfirmCheckout() && $order->order_total > 0 && !$order->payment)
            throw new ApplicationException(lang('igniter.cart::default.checkout.error_invalid_payment'));

        Event::fire('igniter.checkout.afterValidate', [$data, $order]);
    }

    protected function createRules()
    {
        $telephoneRule = 'regex:/^([0-9\s\-\+\(\)]*)$/i';
        if ($this->property('telephoneIsRequired', false))
            $telephoneRule = 'required|'.$telephoneRule;

        $namedRules = [
            ['first_name', 'lang:igniter.cart::default.checkout.label_first_name', 'required|between:1,48'],
            ['last_name', 'lang:igniter.cart::default.checkout.label_last_name', 'required|between:1,48'],
            ['email', 'lang:igniter.cart::default.checkout.label_email', 'sometimes|required|email:filter|max:96|unique:customers'],
            ['telephone', 'lang:igniter.cart::default.checkout.label_telephone', $telephoneRule],
            ['comment', 'lang:igniter.cart::default.checkout.label_comment', 'max:500'],
            ['delivery_comment', 'lang:igniter.cart::default.checkout.label_delivery_comment', 'max:500'],
        ];

        if (Location::orderTypeIsDelivery()) {
            $namedRules[] = ['address_id', 'lang:igniter.cart::default.checkout.label_address', 'required|integer'];
            $namedRules[] = ['address.address_1', 'lang:igniter.cart::default.checkout.label_address_1', 'required|min:3|max:128'];
            $namedRules[] = ['address.address_2', 'lang:igniter.cart::default.checkout.label_address_2', 'nullable|min:3|max:128'];
            $namedRules[] = ['address.city', 'lang:igniter.cart::default.checkout.label_city', 'nullable|min:2|max:128'];
            $namedRules[] = ['address.state', 'lang:igniter.cart::default.checkout.label_state', 'nullable|max:128'];
            $namedRules[] = ['address.postcode', 'lang:igniter.cart::default.checkout.label_postcode', 'nullable|string'];
            $namedRules[] = ['address.country_id', 'lang:igniter.cart::default.checkout.label_country', 'nullable|integer'];
        }

        if ($this->checkoutStep === 'pay' && $this->getOrder()->exists)
            $namedRules = [];

        $namedRules[] = ['payment', 'lang:igniter.cart::default.checkout.label_payment_method', 'sometimes|required|alpha_dash'];
        $namedRules[] = ['terms_condition', 'lang:button_agree_terms', 'sometimes|integer'];

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
            return false;

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

        if (isset($data['address']) && !isset($data['address']['country_id'])) {
            $data['address']['country_id'] = setting('country_id');
        }

        return $data;
    }

    protected function getAgreeTermsPageSlug()
    {
        return $this->getStaticPagePermalink($this->property('agreeTermsPage'));
    }

    public function canConfirmCheckout()
    {
        if (!$this->property('isMultiStepCheckout', false))
            return true;

        if (optional($this->getOrder())->order_total < 1)
            return true;

        return $this->checkoutStep === 'pay';
    }
}
