<?php

declare(strict_types=1);

namespace Igniter\Cart\Models;

use Carbon\CarbonImmutable;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\System\Models\Concerns\Switchable;
use Illuminate\Support\Carbon;

/**
 * Mealtime Model Class
 *
 * @property int $mealtime_id
 * @property string $mealtime_name
 * @property mixed $start_time
 * @property mixed $end_time
 * @property bool $mealtime_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin Model
 */
class Mealtime extends Model
{
    use HasFactory;
    use Locationable;
    use Switchable;

    public const LOCATIONABLE_RELATION = 'locations';

    public const SWITCHABLE_COLUMN = 'mealtime_status';

    /**
     * @var string The database table name
     */
    protected $table = 'mealtimes';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'mealtime_id';

    protected $casts = [
        'start_time' => 'time',
        'end_time' => 'time',
    ];

    public $relation = [
        'morphToMany' => [
            'locations' => [Location::class, 'name' => 'locationable'],
        ],
    ];

    public $timestamps = true;

    public static function getDropdownOptions()
    {
        return self::whereIsEnabled()->dropdown('mealtime_name');
    }

    //
    // Scopes
    //

    public function isAvailable($datetime = null): bool
    {
        $datetime = is_null($datetime)
            ? CarbonImmutable::now()
            : CarbonImmutable::parse($datetime);

        return $datetime->between(
            $datetime->setTimeFromTimeString($this->start_time),
            $datetime->setTimeFromTimeString($this->end_time),
        );
    }

    public function isAvailableNow(): bool
    {
        return $this->isAvailable();
    }
}
