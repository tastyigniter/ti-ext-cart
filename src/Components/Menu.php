<?php

namespace Igniter\Cart\Components;

use Igniter\Cart\Models\Menu as MenuModel;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Location as LocationModel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class Menu extends \Igniter\System\Classes\BaseComponent
{
    use \Igniter\Main\Traits\UsesPage;

    protected $menuListCategories = [];

    public function defineProperties()
    {
        return [
            'isGrouped' => [
                'label' => 'Group menu items list by category',
                'type' => 'switch',
                'validationRule' => 'required|boolean',
            ],
            'collapseCategoriesAfter' => [
                'label' => 'Collapse after how many categories',
                'type' => 'number',
                'default' => 5,
                'validationRule' => 'required|integer',
            ],
            'menusPerPage' => [
                'label' => 'Menus Per Page',
                'type' => 'number',
                'default' => 20,
                'validationRule' => 'required|integer',
            ],
            'showMenuImages' => [
                'label' => 'Show Menu Item Images',
                'type' => 'switch',
                'default' => false,
                'validationRule' => 'required|boolean',
            ],
            'menuImageWidth' => [
                'label' => 'Menu Thumb Width',
                'type' => 'number',
                'span' => 'left',
                'default' => 95,
                'validationRule' => 'integer',
            ],
            'menuImageHeight' => [
                'label' => 'Menu Thumb Height',
                'type' => 'number',
                'span' => 'right',
                'default' => 80,
                'validationRule' => 'integer',
            ],
            'menuCategoryWidth' => [
                'label' => 'Category Thumb Width',
                'type' => 'number',
                'span' => 'left',
                'default' => 1240,
                'validationRule' => 'integer',
            ],
            'menuCategoryHeight' => [
                'label' => 'Category Thumb Height',
                'type' => 'number',
                'span' => 'right',
                'default' => 256,
                'validationRule' => 'integer',
            ],
            'defaultLocationParam' => [
                'label' => 'The default location route parameter (used internally when no location is selected)',
                'type' => 'text',
                'default' => 'local',
                'validationRule' => 'string',
            ],
            'localNotFoundPage' => [
                'label' => 'lang:igniter.local::default.label_redirect',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'home',
                'validationRule' => 'regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'hideMenuSearch' => [
                'label' => 'Hide the menu item search form',
                'type' => 'switch',
                'default' => false,
                'validationRule' => 'required|boolean',
            ],
            'forceRedirect' => [
                'label' => 'Whether to force a page redirect when no location param is present in the request URI.',
                'type' => 'switch',
                'default' => true,
                'validationRule' => 'required|boolean',
            ],
        ];
    }

    public function onRun()
    {
        if ($redirect = $this->checkLocationParam()) {
            return $redirect;
        }

        $this->page['menuIsGrouped'] = !strlen($this->param('category')) && $this->property('isGrouped');
        $this->page['menuCollapseCategoriesAfter'] = $this->property('collapseCategoriesAfter');
        $this->page['showMenuImages'] = $this->property('showMenuImages');
        $this->page['menuImageWidth'] = $this->property('menuImageWidth');
        $this->page['menuImageHeight'] = $this->property('menuImageHeight');
        $this->page['menuCategoryWidth'] = $this->property('menuCategoryWidth', 1240);
        $this->page['menuCategoryHeight'] = $this->property('menuCategoryHeight', 256);
        $this->page['menuAllergenImageWidth'] = $this->property('menuAllergenImageWidth', 28);
        $this->page['menuAllergenImageHeight'] = $this->property('menuAllergenImageHeight', 28);

        $this->page['hideMenuSearch'] = $this->property('hideMenuSearch');
        $this->page['menuSearchTerm'] = $this->getSearchTerm();

        $this->page['menuList'] = $this->loadList();
        $this->page['menuListCategories'] = $this->menuListCategories;
    }

    protected function loadList()
    {
        $location = $this->getLocation();

        $list = MenuModel::with([
            'mealtimes', 'menu_options',
            'categories' => function ($query) use ($location) {
                $query->whereHasOrDoesntHaveLocation($location);
            }, 'categories.media',
            'special', 'allergens', 'media', 'allergens.media',
            'menu_options.option',
        ])->listFrontEnd([
            'page' => $this->param('page'),
            'pageLimit' => $this->property('menusPerPage'),
            'sort' => $this->property('sort', 'menu_priority asc'),
            'location' => $location,
            'category' => $this->param('category'),
            'search' => $this->getSearchTerm(),
            'orderType' => Location::orderType(),
        ]);

        $this->mapIntoObjects($list);

        if (!strlen($this->param('category')) && $this->property('isGrouped')) {
            $this->groupListByCategory($list);
        }

        return $list;
    }

    protected function mapIntoObjects($list)
    {
        $collection = $list->getCollection()->map(function ($menuItem) {
            return $this->createMenuItemObject($menuItem);
        });

        $list->setCollection($collection);

        return $list;
    }

    protected function getLocation()
    {
        if (!$location = Location::current()) {
            return null;
        }

        return $location->getKey();
    }

    protected function groupListByCategory($list)
    {
        $this->menuListCategories = [];

        $groupedList = [];
        foreach ($list->getCollection() as $menuItemObject) {
            $categories = $menuItemObject->model->categories;
            if (!$categories || $categories->isEmpty()) {
                $groupedList[0][] = $menuItemObject;
                continue;
            }

            foreach ($categories as $category) {
                $this->menuListCategories[$category->getKey()] = $category;
                $groupedList[$category->getKey()][] = $menuItemObject;
            }
        }

        $collection = collect($groupedList)
            ->sortBy(function ($menuItems, $categoryId) {
                if (isset($this->menuListCategories[$categoryId])) {
                    return $this->menuListCategories[$categoryId]->priority;
                }

                return $categoryId;
            });

        $list->setCollection($collection);
    }

    protected function checkLocationParam()
    {
        if (!$this->property('forceRedirect', true)) {
            return;
        }

        $param = $this->param('location', 'local');
        if (is_single_location() && $param === $this->property('defaultLocationParam', 'local')) {
            return;
        }

        if (LocationModel::whereSlug($param)->whereIsEnabled()->exists()) {
            return;
        }

        return Redirect::to($this->controller->pageUrl($this->property('localNotFoundPage')));
    }

    public function getSearchTerm()
    {
        if ($this->property('hideMenuSearch')) {
            return '';
        }

        return Request::query('q');
    }

    public function createMenuItemObject($menuItem)
    {
        $object = new \stdClass();

        $object->specialIsActive = ($menuItem->special && $menuItem->special->active());
        $object->specialDaysRemaining = optional($menuItem->special)->daysRemaining();

        $object->menuPriceBeforeSpecial = $object->menuPrice = $menuItem->menu_price;

        if ($object->specialIsActive) {
            $object->menuPriceBeforeSpecial = $menuItem->menu_price;
            $object->menuPrice = $menuItem->special->getMenuPrice($menuItem->menu_price);
        }

        $object->hasThumb = $menuItem->hasMedia('thumb');
        $object->hasOptions = $menuItem->hasOptions();

        $mealtimes = optional($menuItem->mealtimes)->where('mealtime_status', 1);
        $object->hasMealtime = count($mealtimes);
        $object->mealtimeIsAvailable = $menuItem->isAvailable(Location::orderDateTime());
        $object->mealtimeIsNotAvailable = !$object->mealtimeIsAvailable;

        $object->mealtimeTitles = [];
        foreach ($mealtimes ?? [] as $mealtime) {
            $object->mealtimeTitles[] = sprintf(
                lang('igniter.local::default.text_mealtime'),
                $mealtime->mealtime_name,
                now()->setTimeFromTimeString($mealtime->start_time)->isoFormat(lang('system::lang.moment.time_format')),
                now()->setTimeFromTimeString($mealtime->end_time)->isoFormat(lang('system::lang.moment.time_format'))
            );
        }

        $object->model = $menuItem;

        return $object;
    }
}
