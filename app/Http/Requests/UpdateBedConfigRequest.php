<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBedConfigRequest extends FormRequest {
    public function authorize() {
        return true;
    }

    public function rules() {
        $allowed = ['SEPARATED', 'JOINED'];

        return [
            'bed_config' => ['required', 'string', 'in:' . implode(',', $allowed)],
        ];
    }

    public function messages() {
        return [
            'bed_config.required' => 'Bed config required.',
            'bed_config.in' => 'Invalid bed config.',
        ];
    }
}
