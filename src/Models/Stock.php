<?php

declare(strict_types=1);

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\System\Models\Concerns\SendsMailTemplate;
use Illuminate\Support\Carbon;

/**
 * Stocks Model Class
 *
 * @property int $id
 * @property int $location_id
 * @property int $stockable_id
 * @property string $stockable_type
 * @property string $stockable_type_name
 * @property string $stockable_name
 * @property int|null $quantity
 * @property bool $low_stock_alert
 * @property int $low_stock_threshold
 * @property bool $is_tracked
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $low_stock_alert_sent
 * @mixin Model
 */
class Stock extends Model
{
    use HasFactory;
    use Locationable;
    use SendsMailTemplate;

    public const string STATE_NONE = 'none';

    public const string STATE_IN_STOCK = 'in_stock';

    public const string STATE_RECOUNT = 'recount';

    public const string STATE_RESTOCK = 'restock';

    public const string STATE_SOLD = 'sold';

    public const string STATE_RETURNED = 'returned';

    public const string STATE_WASTE = 'waste';

    /**
     * @var string The database table name
     */
    protected $table = 'stocks';

    protected $guarded = ['quantity'];

    protected $casts = [
        'location_id' => 'integer',
        'related_id' => 'integer',
        'quantity' => 'integer',
        'low_stock_alert' => 'boolean',
        'low_stock_alert_sent' => 'boolean',
        'low_stock_threshold' => 'integer',
        'is_tracked' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'location' => Location::class,
        ],
        'hasMany' => [
            'history' => StockHistory::class,
        ],
        'morphTo' => [
            'stockable' => [],
        ],
    ];

    public $timestamps = true;

    protected array $stockableTypes = [
        'menus' => 'igniter.cart::default.stocks.text_stockable_type_menu',
        'menu_option_values' => 'igniter.cart::default.stocks.text_stockable_type_menu_option_value',
    ];

    public function getStockActionOptions(): array
    {
        return [
            self::STATE_NONE => 'lang:igniter.cart::default.stocks.text_action_none',
            self::STATE_IN_STOCK => 'lang:igniter.cart::default.stocks.text_action_in_stock',
            self::STATE_RETURNED => 'lang:igniter.cart::default.stocks.text_action_returned',
            self::STATE_WASTE => 'lang:igniter.cart::default.stocks.text_action_waste',
            self::STATE_RESTOCK => 'lang:igniter.cart::default.stocks.text_action_restock',
            self::STATE_RECOUNT => 'lang:igniter.cart::default.stocks.text_action_recount',
        ];
    }

    public function getStockableTypeNameAttribute(): string
    {
        return lang($this->stockableTypes[$this->stockable_type] ?? $this->stockable_type);
    }

    public function getStockableNameAttribute()
    {
        return $this->stockable ? $this->stockable->getStockableName() : '';
    }

    //
    // Scopes
    //

    public function scopeApplyStockable($query, $model)
    {
        return $query->where('stockable_type', $model->getMorphClass())
            ->where('stockable_id', $model->getKey());
    }

    //
    // Helpers
    //

    public function updateStock(int $quantity, $state = null, array $options = []): bool
    {
        if ($this->shouldUpdateStock($state)) {
            $stockQty = $this->computeStockQuantity($state, $quantity);

            $history = StockHistory::createHistory($this, $quantity, $state, $options);

            if (in_array($state, [self::STATE_IN_STOCK, self::STATE_RESTOCK, self::STATE_RECOUNT])) {
                $this->low_stock_alert_sent = false;
            }

            $this->quantity = $stockQty;
            $this->saveQuietly();

            if ($this->hasLowStock() && $this->shouldAlertOnLowStock()) {
                $this->mailSend('igniter.cart::mail.low_stock_alert', 'location');

                // Prevent duplicate low stock alerts
                $this->updateQuietly(['low_stock_alert_sent' => true]);
            }

            $this->fireSystemEvent('admin.stock.updated', [$history, $stockQty]);
        }

        return true;
    }

    public function updateStockSold(int $orderId, int $quantity): bool
    {
        return $this->updateStock($quantity, self::STATE_SOLD, [
            'order_id' => $orderId,
        ]);
    }

    public function markAsOutOfStock(): bool
    {
        if ($this->is_tracked) {
            $this->quantity = 0;
            $this->saveQuietly();

            $this->fireSystemEvent('admin.stock.outOfStock', [$this]);

            return true;
        }

        throw new ApplicationException(sprintf(
            lang('igniter.cart::default.stocks.alert_stock_not_tracked'), $this->stockable_name,
        ));
    }

    public function checkStock(int $quantity)
    {
        if (!$this->is_tracked) {
            return true;
        }

        return $this->quantity >= $quantity;
    }

    public function outOfStock(): bool
    {
        return $this->is_tracked && $this->quantity <= 0;
    }

    public function hasLowStock(): bool
    {
        return $this->low_stock_threshold && $this->low_stock_threshold >= $this->quantity;
    }

    protected function shouldUpdateStock($state)
    {
        if (!$this->is_tracked) {
            return false;
        }

        return strlen((string)$state) && $state !== self::STATE_NONE;
    }

    protected function computeStockQuantity($state, int $quantity): int
    {
        $stockQty = 0;
        switch ($state) {
            case self::STATE_IN_STOCK:
            case self::STATE_RESTOCK:
                $stockQty = $this->quantity + $quantity;
                break;
            case self::STATE_RECOUNT:
                $stockQty = $quantity;
                break;
            case self::STATE_SOLD:
            case self::STATE_RETURNED:
            case self::STATE_WASTE:
                $stockQty = $this->quantity - $quantity;
                break;
        }

        return max($stockQty, 0);
    }

    protected function shouldAlertOnLowStock()
    {
        if (!$this->low_stock_alert) {
            return false;
        }

        return !$this->low_stock_alert_sent;
    }

    public function mailGetRecipients($type): array
    {
        return [
            [$this->location->location_email, $this->location->location_name],
        ];
    }

    public function mailGetData(): array
    {
        return [
            'stock_name' => $this->stockable->getStockableName(),
            'location_name' => $this->location->location_name,
            'quantity' => $this->quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'stock' => $this,
        ];
    }
}
