<?php

namespace Igniter\Cart\Http\Controllers;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;

class Ingredients extends AdminController
{
    public array $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => \Igniter\Cart\Models\Ingredient::class,
            'title' => 'lang:igniter.cart::default.ingredients.text_title',
            'emptyMessage' => 'lang:igniter.cart::default.ingredients.text_empty',
            'defaultSort' => ['ingredient_id', 'DESC'],
            'configFile' => 'ingredient',
            'back' => 'menus',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:igniter.cart::default.ingredients.text_form_name',
        'model' => \Igniter\Cart\Models\Ingredient::class,
        'request' => \Igniter\Cart\Requests\IngredientRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'ingredients/edit/{ingredient_id}',
            'redirectClose' => 'ingredients',
            'redirectNew' => 'ingredients/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'ingredients/edit/{ingredient_id}',
            'redirectClose' => 'ingredients',
            'redirectNew' => 'ingredients/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'ingredients',
        ],
        'delete' => [
            'redirect' => 'ingredients',
        ],
        'configFile' => 'ingredient',
    ];

    protected null|string|array $requiredPermissions = 'Admin.Ingredients';

    public static function getSlug()
    {
        return 'ingredients';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('menus', 'restaurant');
    }
}
