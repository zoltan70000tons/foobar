<?php

// app/Http/Requests/UpdateAvatarBadgeRequest.php
namespace App\Http\Requests;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarBadgeRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can(Permissions::EditUsers, $this->route('user'));
    }

    public function rules(): array {
        return [
            'badge.text' => ['nullable', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'badge.background' => ['nullable', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
        ];
    }
    protected function prepareForValidation(): void {
        $badge = $this->input('badge', []);
        foreach (['text', 'background'] as $k) {
            if (!empty($badge[$k])) {
                $v = strtoupper($badge[$k]);
                if ($v[0] !== '#') {
                    $v = '#' . $v;
                }
                $badge[$k] = $v;
            }
        }
        $this->merge(['badge' => $badge]);
    }

    public function messages(): array {
        return [
            'badge.text.regex' => 'Invalid text color. Use #RGB or #RRGGBB.',
            'badge.background.regex' => 'Invalid background color. Use #RGB or #RRGGBB.',
        ];
    }
}
