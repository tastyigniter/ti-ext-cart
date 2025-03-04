<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Models;

use Carbon\Carbon;
use Igniter\Cart\Models\Mealtime;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;
use Igniter\System\Models\Concerns\Switchable;

it('returns enabled mealtime dropdown options', function(): void {
    $count = Mealtime::count();

    Mealtime::factory()->count(3)->create(['mealtime_status' => 0]);

    expect(Mealtime::getDropdownOptions()->all())->toHaveCount($count);
});

it('checks if mealtime is available', function(): void {
    $mealtime = Mealtime::factory()->create([
        'start_time' => '10:00:00',
        'end_time' => '20:00:00',
    ]);

    $datetime = Carbon::createFromTime(15);

    expect($mealtime->isAvailable($datetime))->toBeTrue();
});

it('checks if mealtime is not available', function(): void {
    $mealtime = Mealtime::factory()->create([
        'start_time' => '10:00:00',
        'end_time' => '20:00:00',
    ]);

    $datetime = Carbon::createFromTime(21);

    expect($mealtime->isAvailable($datetime))->toBeFalse();
});

it('checks if mealtime is available now', function(): void {
    $mealtime = Mealtime::factory()->create([
        'start_time' => '10:00:00',
        'end_time' => '20:00:00',
    ]);

    $this->travelTo(now()->setHours(13));

    expect($mealtime->isAvailableNow())->toBeTrue();
});

it('configures mealtime model correctly', function(): void {
    $mealtime = new Mealtime;

    expect(class_uses_recursive($mealtime))
        ->toContain(Locationable::class)
        ->toContain(Switchable::class)
        ->and($mealtime->getTable())->toBe('mealtimes')
        ->and($mealtime->getKeyName())->toBe('mealtime_id')
        ->and($mealtime->getMorphClass())->toBe('mealtimes')
        ->and($mealtime->relation)->toEqual([
            'morphToMany' => [
                'locations' => [Location::class, 'name' => 'locationable'],
            ],
        ]);
});
