<?php namespace SamPoyigi\Cart;

use Event;
use Illuminate\Config\Repository;
use System\Classes\BaseController;
use System\Classes\BaseExtension;

class Extension extends BaseExtension
{
    public function register()
    {
        require __DIR__.'/vendor/autoload.php';

        $this->app['config']['cart'] = new Repository(require __DIR__.'/config/cart.php');
        $this->app->bind('cart', 'SamPoyigi\Cart\Classes\Cart');
    }

    public function registerComponents()
    {
        return [
            'SamPoyigi\Cart\components\Cart' => [
                'code'        => 'cart',
                'name'        => 'lang:cart::default.text_component_title',
                'description' => 'lang:cart::default.text_component_desc',
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Module.CartModule' => [
                'description' => 'Ability to manage cart module',
                'action'      => ['manage'],
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Cart Settings',
                'description' => 'Manage cart settings.',
                'icon'        => '',
                'model'       => 'SamPoyigi\Cart\Models\Settings_model',
                'permissions' => ['Module.CartModule'],
                'url'         => admin_url('extensions/settings/sampoyigi/cart'),
            ],
        ];
    }
}
