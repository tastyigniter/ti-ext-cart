<?php

namespace Igniter\Cart\Models;

use Carbon\CarbonImmutable;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\System\Models\Concerns\Switchable;

/**
 * Mealtime Model Class
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
            'locations' => [\Igniter\Local\Models\Location::class, 'name' => 'locationable'],
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

    public function isAvailable($datetime = null)
    {
        $datetime = is_null($datetime)
            ? CarbonImmutable::now()
            : CarbonImmutable::parse($datetime);

        return $datetime->between(
            $datetime->setTimeFromTimeString($this->start_time),
            $datetime->setTimeFromTimeString($this->end_time)
        );
    }

    public function isAvailableNow()
    {
        return $this->isAvailable();
    }
}
