<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;

/**
 * Stock History Model Class
 */
class StockHistory extends Model
{
    use HasFactory;

    /**
     * @var string The database table name
     */
    protected $table = 'stock_history';

    protected $casts = [
        'stock_id' => 'integer',
        'user_id' => 'integer',
        'order_id' => 'integer',
        'quantity' => 'integer',
        'occurred_at' => 'datetime',
    ];

    protected $guarded = [];

    protected $appends = ['staff_name', 'state_text', 'created_at_since'];

    public $relation = [
        'belongsTo' => [
            'stock' => \Igniter\Cart\Models\Stock::class,
            'user' => \Igniter\User\Models\User::class,
            'order' => \Igniter\Cart\Models\Order::class,
        ],
    ];

    public $timestamps = true;

    public static function createHistory(Stock $stock, int $quantity, $state, array $options = [])
    {
        $model = new static;
        $model->stock_id = $stock->getKey();
        $model->user_id = array_get($options, 'staff_id', array_get($options, 'user_id'));
        $model->order_id = array_get($options, 'order_id');
        $model->quantity = $quantity;
        $model->state = $state;
        $model->save();

        return $model;
    }

    public function getStaffNameAttribute()
    {
        return $this->user->name ?? null;
    }

    public function getStateTextAttribute()
    {
        return lang('igniter.cart::default.stocks.text_action_'.$this->state);
    }

    public function getCreatedAtSinceAttribute()
    {
        return $this->created_at ? time_elapsed($this->created_at) : null;
    }
}
