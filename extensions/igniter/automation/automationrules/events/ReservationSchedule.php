<?php

namespace Igniter\Automation\AutomationRules\Events;

use Admin\Models\Reservations_model;
use Igniter\Automation\Classes\BaseEvent;

class ReservationSchedule extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Reservation Hourly Schedule',
            'description' => 'Performed on all recent reservations once every hour',
            'group' => 'order',
        ];
    }

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $params = [];
        $reservation = array_get($args, 0);
        if ($reservation instanceof Reservations_model) {
            $params = $reservation->mailGetData();
        }

        return $params;
    }
}
