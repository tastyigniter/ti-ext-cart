<?php

namespace Igniter\Cart\Models;

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
use Igniter\Flame\Database\Casts\Serialize;
use Igniter\Flame\Database\Model;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Main\Classes\MainController;
use Igniter\PayRegister\Models\PaymentLog;
use Igniter\System\Traits\SendsMailTemplate;
use Igniter\User\Models\Concerns\Assignable;
use Igniter\User\Models\Concerns\HasCustomer;

/**
 * Order Model Class
 */
class Order extends Model
{
    use HasCustomer;
    use HasInvoice;
    use ManagesOrderItems;
    use LogsStatusHistory;
    use SendsMailTemplate;
    use Locationable;
    use Assignable;
    use GeneratesHash;

    const DELIVERY = 'delivery';

    const COLLECTION = 'collection';

    /**
     * @var string The database table name
     */
    protected $table = 'orders';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'order_id';

    public $timestamps = true;

    protected $timeFormat = 'H:i';

    public $guarded = ['ip_address', 'user_agent', 'hash', 'total_items', 'order_total'];

    protected $hidden = ['cart'];

    public $appends = ['customer_name', 'order_type_name', 'order_date_time', 'formatted_address'];

    protected $casts = [
        'customer_id' => 'integer',
        'location_id' => 'integer',
        'address_id' => 'integer',
        'total_items' => 'integer',
        'cart' => Serialize::class,
        'order_date' => 'date',
        'order_time' => 'time',
        'order_total' => 'float',
        'notify' => 'boolean',
        'processed' => 'boolean',
        'order_time_is_asap' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'customer' => \Igniter\User\Models\Customer::class,
            'location' => \Igniter\Local\Models\Location::class,
            'address' => \Igniter\User\Models\Address::class,
            'payment_method' => [\Igniter\PayRegister\Models\Payment::class, 'foreignKey' => 'payment', 'otherKey' => 'code'],
        ],
        'hasMany' => [
            'payment_logs' => \Igniter\PayRegister\Models\PaymentLog::class,
            'menus' => \Igniter\Cart\Models\OrderMenu::class,
            'menu_options' => \Igniter\Cart\Models\OrderMenuOptionValue::class,
            'totals' => \Igniter\Cart\Models\OrderTotal::class,
        ],
    ];

    protected array $queryModifierFilters = [
        'customer' => 'applyCustomer',
        'dateTimeFilter' => 'applyDateTimeFilter',
        'location' => 'whereHasLocation',
        'status' => 'whereStatus',
    ];

    protected array $queryModifierSorts = [
        'order_id asc', 'order_id desc',
        'created_at asc', 'created_at desc',
    ];

    protected array $queryModifierSearchableFields = ['order_id', 'first_name', 'last_name', 'email', 'telephone'];

    public function listCustomerAddresses()
    {
        if (!$this->customer) {
            return [];
        }

        return $this->customer->addresses;
    }

    //
    // Accessors & Mutators
    //

    public function getCustomerNameAttribute($value)
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getOrderTypeNameAttribute()
    {
        if (!$this->location) {
            return $this->order_type;
        }

        return optional(
            $this->location->availableOrderTypes()->get($this->order_type)
        )->getLabel();
    }

    public function getOrderDatetimeAttribute($value)
    {
        if (!isset($this->attributes['order_date'])
            && !isset($this->attributes['order_time'])
        ) {
            return null;
        }

        return make_carbon($this->attributes['order_date'])
            ->setTimeFromTimeString($this->attributes['order_time']);
    }

    public function getFormattedAddressAttribute($value)
    {
        return $this->address ? $this->address->formatted_address : null;
    }

    //
    // Helpers
    //

    public function getUrl($page, $params = [])
    {
        $defaults = [
            'id' => $this->getKey(),
            'hash' => $this->hash,
        ];

        $params = !is_null($params)
            ? array_merge($defaults, $params)
            : [];

        return page_url($page, $params);
    }

    public function isCompleted()
    {
        if (!$this->isPaymentProcessed()) {
            return false;
        }

        return $this->hasStatus(setting('completed_order_status'));
    }

    public function isCanceled()
    {
        return $this->hasStatus(setting('canceled_order_status'));
    }

    public function isCancelable()
    {
        if (!$timeout = $this->location->getOrderCancellationTimeout($this->order_type)) {
            return false;
        }

        if (!$this->order_datetime->isFuture()) {
            return false;
        }

        return $this->order_datetime->diffInRealMinutes() > $timeout;
    }

    /**
     * Check if an order was successfully placed
     *
     * @param int $order_id
     *
     * @return bool TRUE on success, or FALSE on failure
     */
    public function isPaymentProcessed()
    {
        return $this->processed && !empty($this->status_id);
    }

    public function isDeliveryType()
    {
        return $this->order_type == static::DELIVERY;
    }

    public function isCollectionType()
    {
        return $this->order_type == static::COLLECTION;
    }

    /**
     * Return the dates of all orders
     *
     * @return array
     */
    public function getOrderDates()
    {
        return $this->pluckDates('created_at');
    }

    public function markAsCanceled()
    {
        $canceled = false;
        if ($this->addStatusHistory(setting('canceled_order_status'))) {
            $canceled = true;
            OrderCanceledEvent::dispatch($this);
        }

        return $canceled;
    }

    public function markAsPaymentProcessed()
    {
        if (!$this->processed) {
            OrderBeforePaymentProcessedEvent::dispatch($this);

            $this->processed = 1;
            $this->save();

            OrderPaymentProcessedEvent::dispatch($this);
        }

        return $this->processed;
    }

    public function logPaymentAttempt($message, $isSuccess, $request = [], $response = [], $isRefundable = false)
    {
        PaymentLog::logAttempt($this, $message, $isSuccess, $request, $response, $isRefundable);
    }

    public function updateOrderStatus($id, $options = [])
    {
        $id = $id ?: $this->status_id ?: setting('default_order_status');

        return $this->addStatusHistory(
            Status::find($id), $options
        );
    }

    public function getMorphClass()
    {
        return 'orders';
    }

    //
    // Mail
    //

    public function mailGetRecipients($type)
    {
        $emailSetting = setting('order_email');
        is_array($emailSetting) || $emailSetting = [];

        $recipients = [];
        if (in_array($type, $emailSetting)) {
            switch ($type) {
                case 'customer':
                    $recipients[] = [$this->email, $this->customer_name];
                    break;
                case 'location':
                    $recipients[] = [$this->location->location_email, $this->location->location_name];
                    break;
                case 'admin':
                    $recipients[] = [setting('site_email'), setting('site_name')];
                    break;
            }
        }

        return $recipients;
    }

    public function mailGetReplyTo($type)
    {
        $replyTo = [];
        if (in_array($type, (array)setting('order_email', []))) {
            switch ($type) {
                case 'location':
                case 'admin':
                    $replyTo = [$this->email, $this->customer_name];
                    break;
            }
        }

        return $replyTo;
    }

    /**
     * Return the order data to build mail template
     *
     * @return array
     */
    public function mailGetData()
    {
        $model = $this->fresh();

        $data = $model->toArray();
        $data['order'] = $model;
        $data['order_number'] = $model->order_id;
        $data['order_id'] = $model->order_id;
        $data['first_name'] = $model->first_name;
        $data['last_name'] = $model->last_name;
        $data['customer_name'] = $model->customer_name;
        $data['email'] = $model->email;
        $data['telephone'] = $model->telephone;
        $data['order_comment'] = $model->comment;

        $data['order_type'] = $model->order_type_name;
        $data['order_time'] = Carbon::createFromTimeString($model->order_time)->isoFormat(lang('system::lang.moment.time_format'));
        $data['order_date'] = $model->order_date->isoFormat(lang('system::lang.moment.date_format'));
        $data['order_added'] = $model->created_at->isoFormat(lang('system::lang.moment.date_time_format'));

        $data['invoice_id'] = $model->invoice_number;
        $data['invoice_number'] = $model->invoice_number;
        $data['invoice_date'] = $model->invoice_date ? $model->invoice_date->isoFormat(lang('system::lang.moment.date_format')) : null;

        $data['order_payment'] = $model->payment_method->name ?? lang('igniter.cart::default.orders.text_no_payment');

        $data['order_menus'] = [];
        $menus = $model->getOrderMenusWithOptions();
        foreach ($menus as $menu) {
            $optionData = [];
            foreach ($menu->menu_options->groupBy('order_option_group') as $menuItemOptionGroupName => $menuItemOptions) {
                $optionData[] = $menuItemOptionGroupName;
                foreach ($menuItemOptions as $menuItemOption) {
                    $optionData[] = $menuItemOption->quantity
                        .'&nbsp;'.lang('igniter::admin.text_times').'&nbsp;'
                        .$menuItemOption->order_option_name
                        .lang('igniter::admin.text_equals')
                        .currency_format($menuItemOption->quantity * $menuItemOption->order_option_price);
                }
            }

            $data['order_menus'][] = [
                'menu_name' => $menu->name,
                'menu_quantity' => $menu->quantity,
                'menu_price' => currency_format($menu->price),
                'menu_subtotal' => currency_format($menu->subtotal),
                'menu_options' => implode('<br /> ', $optionData),
                'menu_comment' => $menu->comment,
            ];
        }

        $data['order_totals'] = [];
        $orderTotals = $model->getOrderTotals();
        foreach ($orderTotals as $total) {
            $data['order_totals'][] = [
                'order_total_title' => htmlspecialchars_decode($total->title),
                'order_total_value' => currency_format($total->value),
                'priority' => $total->priority,
            ];
        }

        $data['order_address'] = lang('igniter.cart::default.orders.text_collection_order_type');
        if ($model->address) {
            $data['order_address'] = format_address($model->address->toArray(), false);
        }

        if ($model->location) {
            $data['location_logo'] = $model->location->thumb;
            $data['location_name'] = $model->location->location_name;
            $data['location_email'] = $model->location->location_email;
            $data['location_telephone'] = $model->location->location_telephone;
            $data['location_address'] = format_address($model->location->getAddress());
        }

        $statusHistory = StatusHistory::applyRelated($model)->whereStatusIsLatest($model->status_id)->first();
        $data['status_name'] = $statusHistory ? optional($statusHistory->status)->status_name : null;
        $data['status_comment'] = $statusHistory ? $statusHistory->comment : null;

        $controller = MainController::getController() ?: new MainController;
        $data['order_view_url'] = $controller->pageUrl('account/order', [
            'hash' => $model->hash,
        ]);

        return $data;
    }
}
