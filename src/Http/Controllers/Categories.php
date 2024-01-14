<?php

namespace Igniter\Cart\Http\Controllers;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Cart\Models\Category;

class Categories extends AdminController
{
    public array $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => \Igniter\Cart\Models\Category::class,
            'title' => 'lang:igniter.cart::default.categories.text_title',
            'emptyMessage' => 'lang:igniter.cart::default.categories.text_empty',
            'defaultSort' => ['category_id', 'DESC'],
            'configFile' => 'category',
            'back' => 'menus',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:igniter.cart::default.categories.text_form_name',
        'model' => \Igniter\Cart\Models\Category::class,
        'request' => \Igniter\Cart\Requests\CategoryRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'categories/edit/{category_id}',
            'redirectClose' => 'categories',
            'redirectNew' => 'categories/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'categories/edit/{category_id}',
            'redirectClose' => 'categories',
            'redirectNew' => 'categories/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'categories',
        ],
        'delete' => [
            'redirect' => 'categories',
        ],
        'configFile' => 'category',
    ];

    protected null|string|array $requiredPermissions = ['Admin.Categories'];

    public static function getSlug()
    {
        return 'categories';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('menus', 'restaurant');
    }

    public function formBeforeSave($model)
    {
        if (!$model->getRgt() || !$model->getLft()) {
            $model->fixTree();
        }

        if (Category::isBroken()) {
            Category::fixTree();
        }
    }
}
