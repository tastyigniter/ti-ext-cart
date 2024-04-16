<?php

namespace Igniter\Automation;

use Admin\Widgets\Form;
use Igniter\Automation\Classes\EventManager;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseExtension;

/**
 * Automation Extension Information File
 */
class Extension extends BaseExtension
{
    public function register()
    {
        $this->registerConsoleCommand('automation.cleanup', Console\Cleanup::class);
    }

    public function boot()
    {
        EventManager::bindRules();

        $this->extendActionFormFields();
    }

    public function registerPermissions()
    {
        return [
            'Igniter.Automation.Manage' => [
                'description' => 'Create, modify and delete automations',
                'group' => 'module',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'tools' => [
                'child' => [
                    'automation' => [
                        'priority' => 5,
                        'class' => 'automation',
                        'href' => admin_url('igniter/automation/automations'),
                        'title' => lang('igniter.automation::default.text_title'),
                        'permission' => 'Igniter.Automation.*',
                    ],
                ],
            ],
        ];
    }

    public function registerAutomationRules()
    {
        return [
            'events' => [
                'automation.order.schedule.hourly' => \Igniter\Automation\AutomationRules\Events\OrderSchedule::class,
                'automation.reservation.schedule.hourly' => \Igniter\Automation\AutomationRules\Events\ReservationSchedule::class,
            ],
            'actions' => [
                \Igniter\Automation\AutomationRules\Actions\AssignToGroup::class,
                \Igniter\Automation\AutomationRules\Actions\SendMailTemplate::class,
            ],
            'conditions' => [],
        ];
    }

    /**
     * Registers scheduled tasks that are executed on a regular basis.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function registerSchedule($schedule)
    {
        $schedule->call(function () {
            // Pull orders created within the last 30days
            EventManager::fireOrderScheduleEvents();
        })->name('automation-order-schedule')->withoutOverlapping(5)->runInBackground()->hourly();

        $schedule->call(function () {
            // Pull reservations booked within the last 30days
            EventManager::fireReservationScheduleEvents();
        })->name('automation-reservation-schedule')->withoutOverlapping(5)->runInBackground()->hourly();

        $schedule->command('automation:cleanup')->name('Automation Log Cleanup')->daily();
    }

    protected function extendActionFormFields()
    {
        Event::listen('admin.form.extendFieldsBefore', function (Form $form) {
            if (!$form->getController() instanceof \Igniter\Automation\Controllers\Automations) {
                return;
            }
            if ($form->model instanceof \Igniter\Automation\Models\RuleAction) {
                $form->arrayName .= '[options]';
                $form->fields = array_get($form->model->getFieldConfig(null), 'fields', []);
            }
        });
    }
}
