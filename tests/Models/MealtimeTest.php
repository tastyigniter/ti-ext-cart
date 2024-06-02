<?php

namespace Igniter\Cart\Tests\Models;

use Carbon\Carbon;
use Igniter\Cart\Models\Mealtime;

it('returns enabled mealtime dropdown options', function() {
    $count = Mealtime::count();

    Mealtime::factory()->count(3)->create(['mealtime_status' => 0]);

    expect(Mealtime::getDropdownOptions()->all())->toHaveCount($count);
});

it('checks if mealtime is available', function() {
    $mealtime = Mealtime::factory()->create([
        'start_time' => '10:00:00',
        'end_time' => '20:00:00',
    ]);

    $datetime = Carbon::createFromTime(15);

    expect($mealtime->isAvailable($datetime))->toBeTrue();
});

it('checks if mealtime is not available', function() {
    $mealtime = Mealtime::factory()->create([
        'start_time' => '10:00:00',
        'end_time' => '20:00:00',
    ]);

    $datetime = Carbon::createFromTime(21);

    expect($mealtime->isAvailable($datetime))->toBeFalse();
});

it('checks if mealtime is available now', function() {
    $mealtime = Mealtime::factory()->create([
        'start_time' => '10:00:00',
        'end_time' => '20:00:00',
    ]);

    $this->travelTo(now()->setHours(13));

    expect($mealtime->isAvailableNow())->toBeTrue();
});
