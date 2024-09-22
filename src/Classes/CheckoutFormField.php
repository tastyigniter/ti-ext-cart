<?php

namespace Igniter\Cart\Classes;

use Igniter\Admin\Classes\FormField;

class CheckoutFormField extends FormField
{
    public function getName(null|false|string $arrayName = null): string
    {
        if ($arrayName === null) {
            $arrayName = $this->arrayName;
        }

        if ($arrayName) {
            return $arrayName.'.'.implode('.', name_to_array($this->fieldName));
        }

        return $this->fieldName;
    }

    public function getId(?string $suffix = null): string
    {
        $id = '';
        if ($this->arrayName) {
            $id .= '-'.$this->arrayName;
        }

        $id .= '-'.$this->fieldName;

        if ($suffix) {
            $id .= '-'.$suffix;
        }

        if ($this->idPrefix) {
            $id = $this->idPrefix.'-'.$id;
        }

        return strtolower(name_to_id($id));
    }
}
