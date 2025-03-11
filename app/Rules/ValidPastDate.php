<?php

  namespace App\Rules;

  use Illuminate\Contracts\Validation\Rule;
  use Carbon\Carbon;

  class ValidPastDate implements Rule
  {
    public function passes($attribute, $value): bool
    {
      // Retrieve input values
      $year = request()->input('year');
      $month = request()->input('month');
      $day = request()->input('day');

      // Ensure all parts are valid before checking the date
      if (!$year || !$month || !$day || !checkdate((int)$month, (int)$day, (int)$year)) {
        return false;
      }

      // Convert to a date object
      $inputDate = Carbon::create($year, $month, $day);

      return $inputDate->isPast();
    }

    public function message()
    {
      return 'The selected date must be a valid date in the past.';
    }
  }
