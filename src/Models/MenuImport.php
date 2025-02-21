<?php

declare(strict_types=1);

namespace Igniter\Cart\Models;

use Exception;
use Igniter\Flame\Database\Model;
use IgniterLabs\ImportExport\Models\ImportModel;

/**
 * MenuImport Model Class
 *
 * @property int $menu_id
 * @property string $menu_name
 * @property string $menu_description
 * @property string $menu_price
 * @property int $minimum_qty
 * @property bool $menu_status
 * @property int $menu_priority
 * @property string|null $order_restriction
 * @property string|null $created_at
 * @property string|null $updated_at
 * @mixin Model
 */
class MenuImport extends ImportModel
{
    protected $table = 'menus';

    protected $primaryKey = 'menu_id';

    protected $categoryNameCache = [];

    public function importData($results): void
    {
        foreach ($results as $row => $data) {
            try {
                if (!array_get($data, 'menu_name')) {
                    $this->logSkipped($row, 'Missing menu item name');
                    continue;
                }

                $menuItem = Menu::make();
                if ($this->update_existing) {
                    $menuItem = $this->findDuplicateMenuItem($data) ?: $menuItem;
                }

                $except = ['menu_id', 'categories'];
                foreach (array_except($data, $except) as $attribute => $value) {
                    $menuItem->{$attribute} = $value ?: null;
                }

                $menuExists = $menuItem->exists;
                $menuItem->save();

                if ($categoryIds = $this->getCategoryIdsForMenuItem($data)) {
                    $menuItem->categories()->sync($categoryIds, false);
                }

                $menuExists ? $this->logUpdated() : $this->logCreated();
            } catch (Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    protected function findDuplicateMenuItem($data)
    {
        if ($id = array_get($data, 'menu_id')) {
            return Menu::find($id);
        }

        $menuName = array_get($data, 'menu_name');
        $menuItem = Menu::where('menu_name', $menuName);

        return $menuItem->first();
    }

    protected function getCategoryIdsForMenuItem($data)
    {
        $ids = [];

        $categoryNames = $this->decodeArrayValue(array_get($data, 'categories'));
        foreach ($categoryNames as $name) {
            if (strlen($name = trim((string)$name)) < 1) {
                continue;
            }

            if (isset($this->categoryNameCache[$name])) {
                $ids[] = $this->categoryNameCache[$name];
            } else {
                $newCategory = Category::firstOrCreate(['name' => $name]);
                $ids[] = $this->categoryNameCache[$name] = $newCategory->category_id;
            }
        }

        return $ids;
    }
}
