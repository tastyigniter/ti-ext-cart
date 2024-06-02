<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\HasPermalink;
use Igniter\Flame\Database\Traits\NestedTree;
use Igniter\Flame\Database\Traits\Sortable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\System\Models\Concerns\Switchable;

/**
 * Category Model Class
 */
class Category extends Model
{
    use HasFactory;
    use HasMedia;
    use HasPermalink;
    use Locationable;
    use NestedTree;
    use Sortable;
    use Switchable;

    const SORT_ORDER = 'priority';

    const LOCATIONABLE_RELATION = 'locations';

    /**
     * @var string The database table name
     */
    protected $table = 'categories';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'category_id';

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'parent_id' => 'integer',
        'priority' => 'integer',
        'nest_left' => 'integer',
        'nest_right' => 'integer',
    ];

    public $relation = [
        'belongsTo' => [
            'parent_cat' => [\Igniter\Cart\Models\Category::class, 'foreignKey' => 'parent_id', 'otherKey' => 'category_id'],
        ],
        'belongsToMany' => [
            'menus' => [\Igniter\Cart\Models\Menu::class, 'table' => 'menu_categories'],
        ],
        'morphToMany' => [
            'locations' => [\Igniter\Local\Models\Location::class, 'name' => 'locationable'],
        ],
    ];

    public $permalinkable = [
        'permalink_slug' => [
            'source' => 'name',
        ],
    ];

    public $mediable = ['thumb'];

    protected array $queryModifierFilters = [
        'enabled' => ['applySwitchable', 'default' => true],
        'location' => 'whereHasOrDoesntHaveLocation',
    ];

    protected array $queryModifierSorts = ['priority asc', 'priority desc'];

    protected array $queryModifierSearchableFields = ['name', 'description'];

    public static function getDropdownOptions()
    {
        return self::whereIsEnabled()->pluck('name', 'category_id');
    }

    //
    // Accessors & Mutators
    //

    public function getDescriptionAttribute($value)
    {
        return strip_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
    }

    public function getCountMenusAttribute($value)
    {
        return $this->menus()->count();
    }
}
