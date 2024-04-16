<?php

namespace Igniter\Automation\Classes;

abstract class AbstractBase
{
    use \Igniter\Flame\Traits\ExtensionTrait;

    public static function extend(callable $callback)
    {
        self::extensionExtendCallback($callback);
    }
}
