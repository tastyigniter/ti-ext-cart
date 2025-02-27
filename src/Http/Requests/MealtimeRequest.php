<?php

declare(strict_types=1);

namespace Igniter\Cart\Http\Requests;

use Igniter\System\Classes\FormRequest;
use Override;

class MealtimeRequest extends FormRequest
{
    #[Override]
    public function attributes()
    {
        return [
            'mealtime_name' => lang('igniter.cart::default.mealtimes.label_mealtime_name'),
            'start_time' => lang('igniter.cart::default.mealtimes.label_start_time'),
            'end_time' => lang('igniter.cart::default.mealtimes.label_end_time'),
            'mealtime_status' => lang('igniter::admin.label_status'),
            'locations.*' => lang('igniter::admin.label_location'),
        ];
    }

    public function rules(): array
    {
        return [
            'mealtime_name' => ['required', 'string', 'between:2,255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'mealtime_status' => ['required', 'boolean'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer'],
        ];
    }
}
