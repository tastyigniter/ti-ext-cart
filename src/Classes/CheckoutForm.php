<?php

namespace Igniter\Cart\Classes;

use Igniter\Admin\Classes\FormTabs;
use Igniter\Admin\Widgets\Form;

class CheckoutForm extends Form
{
    public function __construct(
        public ?array $config = [],
    ) {}

    public function initialize()
    {
        $this->fillFromConfig([
            'fields',
            'model',
        ]);

        $this->defineFormFields();
    }

    public function validationRules(): array
    {
        $rules = $this->getConfig('rules', []);

        $prefixedKeys = array_map(function($key) {
            return is_string($key) ? 'fields.'.$key : $key;
        }, array_keys($rules));

        return array_combine($prefixedKeys, $rules);
    }

    public function validationMessages(): array
    {
        $messages = $this->getConfig('messages', []);

        $prefixedKeys = array_map(function($key) {
            return is_string($key) ? 'fields.'.$key : $key;
        }, array_keys($messages));

        return array_combine($prefixedKeys, $messages);
    }

    public function validationAttributes(): array
    {
        $attributes = array_map(function($field) {
            return lang($field['label'] ?? $field['name']);
        }, $this->fields);

        $prefixedKeys = array_map(function($key) {
            return is_string($key) ? 'fields.'.$key : $key;
        }, array_keys($attributes));

        return array_combine($prefixedKeys, $attributes);
    }

    protected function defineFormFields()
    {
        if ($this->fieldsDefined) {
            return;
        }

        $this->fireSystemEvent('checkout.form.extendFieldsBefore');

        $this->data ??= $this->model;

        $this->allTabs['primary'] = new FormTabs(FormTabs::SECTION_PRIMARY, []);

        foreach ($this->fields as $name => $config) {
            $label = $config['label'] ?? '';
            $fieldType = $config['type'] ?? 'text';

            $field = new CheckoutFormField($name, $label);
            $field->arrayName = 'fields';
            $field->idPrefix = 'checkout';

            $field->displayAs($fieldType, $config);
            $field->value = $this->getFieldValue($field);

            if (in_array($field->type, ['select', 'radio', 'checkbox'])) {
                $field->options(function() use ($field, $config) {
                    return $this->getOptionsFromModel($field, $config['options'] ?? null);
                });
            }

            $fieldTab = is_array($config) ? array_get($config, 'tab') : null;

            $this->allFields[$name] = $field;

            $this->allTabs['primary']->addField($name, $field, $fieldTab);
        }

        $this->fireSystemEvent('checkout.form.extendFields', [$this->allFields]);

        $this->fieldsDefined = true;
    }
}
