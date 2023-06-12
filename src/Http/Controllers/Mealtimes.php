<?php

namespace Igniter\Cart\Http\Controllers;

use Igniter\Admin\Facades\AdminMenu;

class Mealtimes extends \Igniter\Admin\Classes\AdminController
{
    public $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public $listConfig = [
        'list' => [
            'model' => \Igniter\Cart\Models\Mealtime::class,
            'title' => 'lang:igniter.cart::default.mealtimes.text_title',
            'emptyMessage' => 'lang:igniter.cart::default.mealtimes.text_empty',
            'defaultSort' => ['mealtime_id', 'DESC'],
            'configFile' => 'mealtime',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:igniter.cart::default.mealtimes.text_form_name',
        'model' => \Igniter\Cart\Models\Mealtime::class,
        'request' => \Igniter\Cart\Requests\MealtimeRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'mealtimes/edit/{mealtime_id}',
            'redirectClose' => 'mealtimes',
            'redirectNew' => 'mealtimes/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'mealtimes/edit/{mealtime_id}',
            'redirectClose' => 'mealtimes',
            'redirectNew' => 'mealtimes/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'redirect' => 'mealtimes',
        ],
        'delete' => [
            'redirect' => 'mealtimes',
        ],
        'configFile' => 'mealtime',
    ];

    protected $requiredPermissions = 'Admin.Mealtimes';

    public static function getSlug()
    {
        return 'mealtimes';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('mealtimes', 'restaurant');
    }
}
