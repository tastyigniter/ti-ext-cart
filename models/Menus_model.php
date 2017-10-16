<?php namespace SamPoyigi\Cart\Models;

use Gloudemans\Shoppingcart\Contracts\Buyable;
use Igniter\Models\Menus_model as MenusModel;

//class Menus_model extends Model
class Menus_model extends MenusModel implements Buyable
{
//    protected $table = 'menus';
//
//    /**
//     * @var string The database table primary key
//     */
//    protected $primaryKey = 'menu_id';
//
//    public $relation = [
//        'hasMany'       => [
//            'menu_options'       => ['Igniter\Models\Menu_item_options_model', 'delete' => true],
//            'menu_option_values' => ['Igniter\Models\Menu_item_option_values_model'],
//        ],
//        'hasOne'        => [
//            'special' => ['Igniter\Models\Menus_specials_model', 'delete' => true],
//        ],
//        'belongsTo'     => [
//            'mealtime' => ['Igniter\Models\Mealtimes_model'],
//        ],
//        'belongsToMany' => [
//            'categories' => ['Igniter\Models\Categories_model', 'table' => 'menu_categories', 'delete' => true],
//        ],
//        'morphToMany'   => [
//            'locations' => ['Igniter\Models\Locations_model', 'name' => 'locationable'],
//        ],
//    ];

    /**
     * Get the identifier of the Buyable item.
     *
     * @return int|string
     */
    public function getBuyableIdentifier($options = null)
    {
        return $this->getKey();
    }

    /**
     * Get the description or title of the Buyable item.
     *
     * @return string
     */
    public function getBuyableDescription($options = null)
    {
        return $this->description;
    }

    /**
     * Get the price of the Buyable item.
     *
     * @return float
     */
    public function getBuyablePrice($options = null)
    {
        var_dump($options);

        return $this->menu_price;
    }
}