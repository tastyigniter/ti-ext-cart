<?php

namespace Igniter\Cart\Http\Controllers;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;

class Menus extends AdminController
{
    public array $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => \Igniter\Cart\Models\Menu::class,
            'title' => 'lang:igniter.cart::default.menus.text_title',
            'emptyMessage' => 'lang:igniter.cart::default.menus.text_empty',
            'defaultSort' => ['menu_id', 'DESC'],
            'configFile' => 'menu',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:igniter.cart::default.menus.text_form_name',
        'model' => \Igniter\Cart\Models\Menu::class,
        'request' => \Igniter\Cart\Http\Requests\MenuRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'menus/edit/{menu_id}',
            'redirectClose' => 'menus',
            'redirectNew' => 'menus/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'menus/edit/{menu_id}',
            'redirectClose' => 'menus',
            'redirectNew' => 'menus/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'menus',
        ],
        'delete' => [
            'redirect' => 'menus',
        ],
        'configFile' => 'menu',
    ];

    protected null|string|array $requiredPermissions = 'Admin.Menus';

    public static function getSlug()
    {
        return 'menus';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('menus', 'restaurant');
    }
}
