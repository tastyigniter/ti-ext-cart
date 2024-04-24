---
title: "Cart Extension"
section: "extensions"
sortOrder: 40
---

## Introduction

TastyIgniter Cart extension is simple, flexible shopping cart.

## Features

- Stock control
- Cart conditions: Tip, Tax
- Order types: Delivery, Pick-up
- Abandoned Checkout
- Payment method: Cod, Paypal, Stripe
- One page checkout
- Easy to extends and customize
- Sending order confirmation emails

## Installation

To install this extension, click on the **Add to Site** button on the TastyIgniter marketplace item page or search
for **Igniter.Cart** in **Admin System > Updates > Browse Extensions**

## Admin Panel

In the admin user interface you can manage the cart conditions.

## Registering a new Cart Condition

Here is an example of an extension registering a cart condition.

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

## Automations

### Events

- Order Placed Event
- Order Status Update Event
- Order Assigned Event

### Conditions

- Order Attributes
- Order Status Attributes

## Notifications

- Order confirmation notification
- Order status update notification
- Order assigned notification

## Events

The Cart Library used with this extension will fire some global events that can be useful for interacting with other
extensions.

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
