<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class UniqueActivatedEmail implements ValidationRule {
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        $exists = DB::table('users')
            ->where('email', $value)
            ->whereNotNull('email_verified_at') // Only verified emails
            ->exists();

        if ($exists) {
            $fail(Lang::get('validation.unique_activated_email'));
        }
    }
}
