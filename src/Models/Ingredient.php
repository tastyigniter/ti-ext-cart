<?php

namespace Igniter\Cart\Models;

use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\System\Models\Concerns\Switchable;

/**
 * Ingredients Model Class
 *
 * @property int $ingredient_id
 * @property string $name
 * @property string $description
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_allergen
 * @property-read mixed $count_menus
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Igniter\Flame\Database\Attach\Media> $media
 * @property-read int|null $media_count
 * @mixin \Igniter\Flame\Database\Model
 */
class Ingredient extends Model
{
    use HasFactory;
    use HasMedia;
    use Switchable;

    /**
     * @var string The database table name
     */
    protected $table = 'ingredients';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'ingredient_id';

    protected $guarded = [];

    protected $casts = [
        'is_allergen' => 'boolean',
    ];

    public $relation = [
        'morphedByMany' => [
            'menus' => [\Igniter\Cart\Models\Menu::class, 'name' => 'ingredientable'],
            'menu_option_values' => [\Igniter\Cart\Models\MenuOptionValue::class, 'name' => 'ingredientable'],
        ],
    ];

    public $mediable = ['thumb'];

    public $timestamps = true;

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

    //
    // Scopes
    //

    public function scopeWhereHasMenus($query)
    {
        return $query->whereHas('menus')->whereIsEnabled();
    }

    public function scopeIsAllergen($query)
    {
        return $query->where('is_allergen', 1);
    }
}
