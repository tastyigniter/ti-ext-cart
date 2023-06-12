<?php

namespace Igniter\Cart\Models;

use Exception;
use IgniterLabs\ImportExport\Models\ImportModel;

class MenuImport extends ImportModel
{
    protected $table = 'menus';

    protected $primaryKey = 'menu_id';

    protected $mealtimeNameCache = [];

    protected $categoryNameCache = [];

    public function importData($results)
    {
        foreach ($results as $row => $data) {
            try {
                if (!$name = array_get($data, 'menu_name')) {
                    $this->logSkipped($row, 'Missing menu item name');
                    continue;
                }

                $menuItem = Menu::make();
                if ($this->update_existing) {
                    $menuItem = $this->findDuplicateMenuItem($data) ?: $menuItem;
                }

                $except = ['menu_id', 'categories', 'mealtimes'];
                foreach (array_except($data, $except) as $attribute => $value) {
                    $menuItem->{$attribute} = $value ?: null;
                }

                if ($mealtime = $this->findMealtimeFromName($data)) {
                    $menuItem->mealtime_id = $mealtime->mealtime_id;
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

    protected function findMealtimeFromName($data)
    {
        if (!$name = array_get($data, 'mealtimes')) {
            return null;
        }

        if (isset($this->mealtimeNameCache[$name])) {
            return $this->mealtimeNameCache[$name];
        }

        $mealtime = Mealtime::where('mealtime_name', $name)->first();

        return $this->mealtimeNameCache[$name] = $mealtime;
    }

    protected function getCategoryIdsForMenuItem($data)
    {
        $ids = [];

        $categoryNames = $this->decodeArrayValue(array_get($data, 'categories'));

        foreach ($categoryNames as $name) {
            if (!$name = trim($name)) {
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
