Igniter Cart extension is simple, flexible shopping cart. There are some features:
- Stock control
- Cart conditions: Coupon, Tax
- Order types: Delivery, Pick-up
- Abandoned Checkout
- Payment method: Cod, Paypal, Stripe 
- One page checkout 
- Easy to extends and customize 
- Sending order confirmation emails

This extension requires the following extensions:
- Igniter Local
- Igniter User
- Igniter PayRegister

### Admin Panel
In the admin user interface you can manage the cart conditions.

### Components
| Name     | Page variable                  | Description                                      |
| -------- | ------------------------------ | ------------------------------------------------ |
| CartBox  | `<?= component('cartBox') ?>`  | Show the contents of and manages the user's cart |
| Checkout | `<?= component('checkout') ?>` | Displays Checkout form on the page               |
| Orders | `<?= component('orders') ?>` | Displays a list of orders on the page               |

### CartBox Component

**Properties**

| Property                 | Description              | Example Value | Default Value |
| ------------------------ | ------------------------ | ------------- | ------------- |
| cartBoxTimeFormat                     | Time format            | ddd hh:mm a        | ddd hh:mm a         |
| checkStockCheckout                     | Check cart item stock quantity            | true/false         | false         |
| pageIsCheckout                     | Value to determine if the user is on a checkout page             | true/false         | false         |
| pageIsCart                     | Display a standalone cart             | true/false         | false         |
| checkoutPage                     | Checkout page path            | checkout/checkout         | checkout/checkout         |

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| `$cartBoxTimeFormat` | Delivery and pick-up time format                                                |
| `$pageIsCart` | Display the standalone cartbox                                         |
| `$pageIsCheckout` | CartBox is loaded on the checkout page                             |
| `$cart` | Cart Class instance                                                |
| `$location` | Location Class instance |
| `$locationCurrent` | Location Model instance                                          |

**Example:**

```
---
title: 'Checkout'
permalink: /checkout

'[cartBox]':
    cartBoxTimeFormat: 'ddd hh:mm a'
    checkStockCheckout: 0
    pageIsCheckout: 0
    pageIsCart: 0
    checkoutPage: checkout/checkout
---
...
<?= component('cartBox') ?>
...
```

### Checkout Component

**Properties**

| Property                 | Description              | Example Value | Default Value |
| ------------------------ | ------------------------ | ------------- | ------------- |
| showCountryField                     | Show/hide the country checkout field            | true/false        | false         |
| agreeTermsPage                     | Terms and conditions page            |    page/terms     |          |
| menusPage                     | Menus page            |    page/menus     |          |
| redirectPage                     | Redirect page name           |    checkout/checkout    |    checkout/checkout      |
| orderPage                     | Order page name            |    account/order     |     account/order     |
| successPage                     | Order confirmation page name           |    checkout/success     |     checkout/success     |
| successParamCode                     | URL routing code used for displaying the order confirmation page            | hash       | hash         |

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| `$showCountryField` | Display the country form field                             |
| `$agreeTermsPage` | Link to thenterms & conditions page                                                |
| `$redirectPage` | Link to the checkout cancel page                                                |
| `$menusPage` | Link to the menus page                                                |
| `$successPage` | Link to the confirmation page                                                |
| `$order` | Order Model instance                                          |
| `$paymentGateways` | Instances of available payment gateways                                          |

**Example:**

```
---
title: 'Checkout'
permalink: /checkout

'[checkout]':
    showCountryField: 0
    menusPage: local/menus
    redirectPage: checkout/checkout
    successPage: checkout/success
    successParamCode: 'hash'
---
...
<?= component('checkout') ?>
...
```

### Registering a new Cart Condition

Here is an example of an extension registering a cart condition
.

```
public function registerCartConditions()
{
    return [
        \Igniter\Local\CartConditions\Tip::class => [
            'name' => 'tip',
            'label' => 'Tip',
            'description' => 'Applies tips to cart total',
        ]
    ];
}
```

### Event Rules

## Events
- Order Placed Event
- Order Status Update Event
- Order Assigned Event

## Conditions
- Order Attributes
- Order Status Attributes

### Notifications

- Order confirmation notification
- Order status update notification
- Order assigned notification

### Events

The Cart Library used with this extension will fire some global events that can be useful for interacting with other extensions.

| Event | Description | Parameters |
| ----- | ----------- | ---------- |
| `cart.created` |    When cart instance is created.    |           |
| `cart.added` |      When an item has been added to the cart.       |      The `CartItem` instance     |
| `cart.updated` |     When an item has been updated in the cart.     |      The `CartItem` instance     |
| `cart.removed` |    When an item has been removed from the cart.      |     The `CartItem` instance      |
| `cart.cleared` |     When all items has been cleared from the cart.     |           |
| `cart.condition.loaded` |      When a condition has been loaded to the cart.    |      The `CartCondition` instance     |
| `cart.condition.removed` |     When a condition has been removed from the cart.     |     The `CartCondition` instance      |
| `cart.condition.cleared` |      When all condition has been cleared from the cart.    |           |
| `cart.stored` |    When the content of a cart was stored.      |           |
| `cart.restored` |      When the content of a cart was restored.    |           |

**Example of hooking an event**

```
Event::listen('cart.updated', function($cartItem) {
    // ...
});
```

### License
[The MIT License (MIT)](https://tastyigniter.com/licence/)