<?php

namespace Igniter\Cart\Tests\Models;

use Carbon\Carbon;
use Igniter\Admin\Models\Concerns\GeneratesHash;
use Igniter\Admin\Models\Status;
use Igniter\Admin\Models\StatusHistory;
use Igniter\Admin\Traits\LogsStatusHistory;
use Igniter\Cart\Events\OrderBeforePaymentProcessedEvent;
use Igniter\Cart\Events\OrderCanceledEvent;
use Igniter\Cart\Events\OrderPaymentProcessedEvent;
use Igniter\Cart\Models\Concerns\HasInvoice;
use Igniter\Cart\Models\Concerns\ManagesOrderItems;
use Igniter\Cart\Models\Order;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\PayRegister\Models\Payment;
use Igniter\System\Models\Country;
use Igniter\System\Traits\SendsMailTemplate;
use Igniter\User\Models\Address;
use Igniter\User\Models\Concerns\Assignable;
use Igniter\User\Models\Concerns\HasCustomer;
use Illuminate\Support\Facades\Event;

it('gets customer name attribute correctly', function() {
    $order = Order::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($order->customer_name)->toBe('John Doe');
});

it('gets order type name attribute correctly', function() {
    $location = Location::factory()->create();
    $order = Order::factory()->for($location)->create([
        'order_type' => Order::DELIVERY,
    ]);

    expect($order->order_type_name)->toBe(lang('igniter.local::default.text_delivery'));
});

it('gets order datetime attribute correctly', function() {
    $order = Order::factory()->create([
        'order_date' => '2021-01-01',
        'order_time' => '12:00',
    ]);

    expect($order->order_datetime)->toBeInstanceOf(Carbon::class)
        ->and($order->order_datetime->format('Y-m-d H:i'))->toBe('2021-01-01 12:00');
});

it('gets formatted address attribute correctly', function() {
    $order = Order::factory()
        ->for(Address::factory()->create([
            'address_1' => '123 Main St',
            'address_2' => 'Apt. 035',
            'city' => 'City',
            'state' => 'State',
            'postcode' => '12345',
            'country_id' => Country::factory()->state(['status' => 1, 'country_name' => 'Country']),
        ]))
        ->create();

    expect($order->formatted_address)->toBe("123 Main St\nApt. 035\nCity 12345\nCountry");
});

it('checks if order is completed', function() {
    $status = Status::factory()->create();
    $order = Order::factory()->create([
        'processed' => 1,
        'status_id' => $status->getKey(),
    ]);

    setting()->set(['completed_order_status' => $status->getKey()]);
    $order->updateOrderStatus($status->getKey());

    expect($order->isCompleted())->toBeTrue();
});

it('checks if order is not completed', function() {
    $status = Status::factory()->create();
    $order = Order::factory()->create([
        'processed' => 1,
        'status_id' => $status->getKey(),
    ]);

    expect($order->isCompleted())->toBeFalse();
});

it('checks if order is canceled', function() {
    $status = Status::factory()->create();
    $order = Order::factory()->create([
        'processed' => 1,
        'status_id' => $status->getKey(),
    ]);

    setting()->set(['canceled_order_status' => $status->getKey()]);
    $order->updateOrderStatus($status->getKey());

    expect($order->isCanceled())->toBeTrue();
});

it('checks if order is not canceled', function() {
    $status = Status::factory()->create();
    $order = Order::factory()->create([
        'processed' => 1,
        'status_id' => $status->getKey(),
    ]);

    expect($order->isCanceled())->toBeFalse();
});

it('checks if order is delivery type', function() {
    $order = Order::factory()->create([
        'order_type' => Order::DELIVERY,
    ]);

    expect($order->isDeliveryType())->toBeTrue();
});

it('checks if order is collection type', function() {
    $order = Order::factory()->create([
        'order_type' => Order::COLLECTION,
    ]);

    expect($order->isCollectionType())->toBeTrue();
});

it('checks if order payment is processed', function() {
    $order = Order::factory()->create([
        'processed' => 1,
        'status_id' => 1,
    ]);

    expect($order->isPaymentProcessed())->toBeTrue();
});

it('checks if order payment is not processed', function() {
    $order = Order::factory()->create([
        'processed' => 0,
        'status_id' => 0,
    ]);

    expect($order->isPaymentProcessed())->toBeFalse();
});

it('marks order as canceled correctly', function() {
    Event::fake();

    $status = Status::factory()->create();
    $order = Order::factory()->create([
        'processed' => 1,
        'status_id' => $status->getKey(),
    ]);

    setting()->set(['canceled_order_status' => $status->getKey()]);
    $order->updateOrderStatus($status->getKey());

    expect($order->markAsCanceled())->toBeTrue();
    Event::assertDispatched(OrderCanceledEvent::class);
});

it('marks order payment as processed correctly', function() {
    Event::fake();

    $order = Order::factory()->create();

    expect($order->markAsPaymentProcessed())->toBeTrue();
    Event::assertDispatched(OrderBeforePaymentProcessedEvent::class);
    Event::assertDispatched(OrderPaymentProcessedEvent::class);
});

it('logs payment attempt correctly', function() {
    $order = Order::factory()
        ->for(Payment::factory()->create(), 'payment_method')
        ->create();

    $order->logPaymentAttempt('Payment processed', true);

    expect($order->payment_logs()->count())->toBe(1);
});

it('updates order status correctly', function() {
    $order = Order::factory()->create();

    $status = Status::factory()->create();
    expect($order->updateOrderStatus($status->getKey()))->toBeInstanceOf(StatusHistory::class);
});

it('gets mail recipients correctly for customer type', function() {
    $order = Order::factory()->create([
        'email' => 'customer@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    setting()->set(['order_email' => ['customer']]);

    $recipients = $order->mailGetRecipients('customer');

    expect($recipients)->toBe([[$order->email, $order->customer_name]]);
});

it('gets mail recipients correctly for location type', function() {
    $location = Location::factory()->create();
    $order = Order::factory()->for($location)->create();

    setting()->set(['order_email' => ['location']]);

    $recipients = $order->mailGetRecipients('location');

    expect($recipients)->toBe([[$location->location_email, $location->location_name]]);
});

it('gets mail recipients correctly for admin type', function() {
    $order = Order::factory()->create();

    setting()->set(['order_email' => ['admin']]);

    $recipients = $order->mailGetRecipients('admin');

    expect($recipients)->toBe([[setting('site_email'), setting('site_name')]]);
});

it('applies filters on the query builder', function() {
    $query = Order::query()->applyFilters([
        'customer' => 1,
        'dateTimeFilter' => 1,
        'location' => 1,
        'status' => 1,
        'sort' => 'order_date desc',
        'search' => 'John Doe',
    ]);

    expect($query->toSql())
        ->toContain('and `status_id` in (?)')
        ->toContain('and `location_id` in (?)')
        ->toContain('and `orders`.`customer_id` = ?')
        ->toContain('ADDTIME(order_date, order_time) between ? and ?')
        ->toContain('lower(first_name)', 'lower(last_name)', 'lower(email)')
        ->toContain('order by `order_date` desc');
});

it('generates order hash on create correctly', function() {
    $order = Order::factory()->create();

    expect($order->hash)->not->toBeEmpty();
});

it('configures order model correctly', function() {
    $order = new Order;

    expect(class_uses($order))
        ->toHaveKey(Assignable::class)
        ->toHaveKey(GeneratesHash::class)
        ->toHaveKey(HasCustomer::class)
        ->toHaveKey(HasInvoice::class)
        ->toHaveKey(Locationable::class)
        ->toHaveKey(LogsStatusHistory::class)
        ->toHaveKey(ManagesOrderItems::class)
        ->toHaveKey(SendsMailTemplate::class)
        ->and($order->getTable())->toBe('orders')
        ->and($order->getKeyName())->toBe('order_id')
        ->and($order->getGuarded())->toEqual([
            'ip_address', 'user_agent', 'hash', 'total_items', 'order_total',
        ])
        ->and($order->getAppends())->toEqual([
            'customer_name', 'order_type_name', 'order_date_time', 'formatted_address', 'status_name',
        ])
        ->and($order->getHidden())->toEqual(['cart'])
        ->and($order->timestamps)->toBeTrue();
});
