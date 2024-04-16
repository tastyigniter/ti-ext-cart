<?php

namespace Igniter\Automation\Controllers;

use Admin\Facades\AdminMenu;
use Igniter\Automation\Models\AutomationRule;
use Igniter\Flame\Exception\ApplicationException;

/**
 * Automation Admin Controller
 */
class Automations extends \Admin\Classes\AdminController
{
    public $implement = [
        \Admin\Actions\FormController::class,
        \Admin\Actions\ListController::class,
    ];

    public $listConfig = [
        'list' => [
            'model' => \Igniter\Automation\Models\AutomationRule::class,
            'title' => 'lang:igniter.automation::default.text_title',
            'emptyMessage' => 'lang:igniter.automation::default.text_empty',
            'defaultSort' => ['id', 'DESC'],
            'configFile' => 'automationrule',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:igniter.automation::default.text_form_name',
        'model' => \Igniter\Automation\Models\AutomationRule::class,
        'create' => [
            'title' => 'lang:admin::lang.form.create_title',
            'redirect' => 'igniter/automation/automations/edit/{id}',
            'redirectClose' => 'igniter/automation/automations',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'igniter/automation/automations/edit/{id}',
            'redirectClose' => 'igniter/automation/automations',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'igniter/automation/automations',
        ],
        'delete' => [
            'redirect' => 'igniter/automation/automations',
        ],
        'configFile' => 'automationrule',
    ];

    protected $requiredPermissions = 'Igniter.Automation.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tools', 'automation');
    }

    public function index()
    {
        if ($this->getUser()->hasPermission('Igniter.Automation.Manage')) {
            AutomationRule::syncAll();
        }

        $this->asExtension('ListController')->index();
    }

    public function edit_onLoadCreateActionForm($context, $recordId)
    {
        return $this->loadConnectorFormField('actions', $context, $recordId);
    }

    public function edit_onLoadCreateConditionForm($context, $recordId)
    {
        return $this->loadConnectorFormField('conditions', $context, $recordId);
    }

    public function formExtendFields($form)
    {
        if ($form->context != 'create') {
            $form->getField('event_class')->disabled = true;
        }
    }

    public function formBeforeCreate($model)
    {
        $model->is_custom = true;
        $model->status = true;
    }

    public function formValidate($model, $form)
    {
        $rules = [
            ['event_class', 'lang:igniter.automation::default.label_event_class', 'sometimes|required'],
        ];

        return $this->validatePasses(post($form->arrayName), $rules);
    }

    protected function loadConnectorFormField($method, $context, $recordId): array
    {
        $actionClass = post('AutomationRule._'.str_singular($method));
        if (!strlen($actionClass)) {
            throw new ApplicationException(sprintf('Please select an %s to attach', str_singular($method)));
        }

        $formController = $this->asExtension('FormController');
        $model = $formController->formFindModelObject($recordId);

        $model->$method()->create([
            'class_name' => $actionClass,
            'automation_rule_id' => $recordId,
        ]);

        $formController->initForm($model, $context);
        $formField = $this->widgets['form']->getField($method);

        return [
            '#notification' => $this->makePartial('flash'),
            '#'.$formField->getId('group') => $this->widgets['form']->renderField($formField, [
                'useContainer' => false,
            ]),
        ];
    }
}
