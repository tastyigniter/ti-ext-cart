<?php

declare(strict_types=1);

namespace Igniter\Cart\Http\Controllers;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\ListController;
use Igniter\Cart\Models\Stock;
use Igniter\Local\Http\Actions\LocationAwareController;

class Inventory extends AdminController
{
    public array $implement = [
        ListController::class,
        LocationAwareController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => Stock::class,
            'title' => 'lang:igniter.cart::default.stocks.text_inventory_title',
            'emptyMessage' => 'lang:igniter.cart::default.stocks.text_empty',
            'defaultSort' => ['updated_at', 'DESC'],
            'configFile' => 'inventory',
        ],
    ];

    protected null|string|array $requiredPermissions = 'Admin.Inventory';

    public static function getSlug(): string
    {
        return 'inventory';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('inventory', 'restaurant');
    }

    public function listExtendQuery($query): void
    {
        $query->where('is_tracked', true);
    }
}
