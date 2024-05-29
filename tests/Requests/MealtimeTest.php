<?php

namespace Tests\Requests;

use Igniter\Cart\Http\Requests\MealtimeRequest;

it('has required rule for inputs', function() {
    expect('required')->toBeIn(array_get((new MealtimeRequest)->rules(), 'mealtime_name'))
        ->and('required')->toBeIn(array_get((new MealtimeRequest)->rules(), 'start_time'))
        ->and('required')->toBeIn(array_get((new MealtimeRequest)->rules(), 'end_time'))
        ->and('required')->toBeIn(array_get((new MealtimeRequest)->rules(), 'mealtime_status'));
});

it('has max characters rule for mealtime_name input', function() {
    expect('between:2,255')->toBeIn(array_get((new MealtimeRequest)->rules(), 'mealtime_name'));
});

it('has date_format:H:i rule for start_time and end_time input', function() {
    expect('date_format:H:i')->toBeIn(array_get((new MealtimeRequest)->rules(), 'start_time'))
        ->and('date_format:H:i')->toBeIn(array_get((new MealtimeRequest)->rules(), 'end_time'));
});
