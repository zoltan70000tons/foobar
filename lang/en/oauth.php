<?php

return [
  /*
    |--------------------------------------------------------------------------
    | OAuth Language Lines EN
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during OAuth for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

  'Auth' => [
    'login_title' => 'Sign In',
    'register_title' => 'Sign Up',
    'account_credentials_title' => 'Account credentials',
    'personal_information_title' => 'Personal information',
    'reset_password_title' => 'Reset password',
    'verify_email_title' => 'Verify eMail',
    'login' => 'Sign in',
    'signIn' => 'Sign in',
    'identifier' => 'eMail or Survivor Number',
    'recover_account_instructions' => 'Have you sailed with us before? Link your eMail to your Survivor Number!',
    'account_recovered' =>
      'Your Survivor Number has been successfully linked to your eMail. Please check your eMail to complete the process.',
    'logout' => 'Log Out',
    'register' => 'Sign Up',
    'survivor_number' => 'Survivor number',
    'email' => 'eMail',
    'confirm_email' => 'Confirm eMail',
    'country' => 'Country',
    'loginId' => 'Your eMail',
    'password' => 'Password',
    'password_confirmation' => 'Confirm Password',
    'old_password' => 'Old password',
    'new_password' => 'New password',
    'confirm_new_password' => 'Confirm new password',
    'remember_me' => 'Remember me',
    'forgot_password' => 'Forgot your password?',
    'name' => 'First name',
    'middle_name' => 'Middle name',
    'surname' => 'Last name',
    'gender' => 'Gender',
    'dob' => 'Date of birth',
    'month' => 'Month',
    'day' => 'Day',
    'year' => 'Year',
    'address' => 'Address',
    'address1' => 'Address Line 1',
    'address2' => 'Address Line 2',
    'city' => 'City',
    'state' => 'State / Province / Region',
    'zip_code' => 'Zip Code',
    'reset_password' => 'Reset password',
    'send_password_reset_link' => 'Send password reset link',
    'send_email' => 'Send eMail',
    'confirm_password' => 'Confirm password',
    'verify_email' => 'Verify eMail',
    'verify_email_sent' => 'A fresh verification link has been sent to your eMail address.',
    'check_email' =>
      'Almost there! Before proceeding, please check your eMail for a verification link. If you did not see the email, please check your spam folder.',
    'receive_email' => 'If you did not receive the eMail, click here to request another.',
    'link_expired' => 'Your verification link has expired.',
    'account_terms' => 'I agree to the <terms>Terms and Conditons</terms> and <privacy>Privacy Policy</privacy>.',
    'marketing' => 'I agree to receive marketing eMails',
    'dont_have_account' => "Don't have an account?",
    'have_account' => 'Already have an account?',
    'login_to_your' => 'Please use your eMail or Survivor Number to access your account.',
    'phone' => 'Phone',
    'citizenship' => 'Citizenship',
    'emergency_contact' => 'Emergency Contact Name',
    'emergency_phone' => 'Emergency Contact phone',
    'link_sent' => 'Link sent successfully',
    'password_helper' => 'Password must be at least 8 characters long and contain one special character from:',
    'min_length' => 'Minimum Length',
    'special_chars' => 'Contains Special Character',

    'UpdateEmail' => [
      'title' => 'Change your default eMail address',
      'disclaimer' =>
        'Please note that your default eMail address is the one you use to log in to your account. It will also be the default eMail address on the booking platform.',
    ],

    'Error' => [
      'general' => [
        'special' => 'Special characters are not allowed.',
      ],
      'invalid_session_request' => 'Invalid request or time expired, please refresh the page and try again.',
      'identifier' => [
        'required' => 'eMail or Survivor number is required',
        'email' => 'eMail must be a valid eMail address',
        'invalid' => 'Invalid credentials',
      ],
      'email' => [
        'required' => 'eMail is required',
        'email' => 'eMail must be a valid eMail address',
        'invalid' => 'Invalid eMail or password',
      ],
      'survivor_number' => [
        'required' => 'Survivor number is required',
        'min' => 'Survivor number must be at least 9 characters',
        'invalid' => 'Survivor number must be a 9-digit numeric code.',
      ],
      'password' => [
        'required' => 'Password is required',
        'min' => 'Password must be at least 8 characters',
        'special_char' => 'Password must contain at least one special character',
      ],
      'privacy_policy' => [
        'required' => 'You must agree to the Terms and Conditions and Privacy Policy.',
      ],
      'password_confirmation' => [
        'required' => 'Password confirmation is required',
        'same' => 'Password confirmation must match password',
      ],
      'name' => [
        'required' => 'First Name is required',
        'invalid' => 'Username cannot contain special characters',
        'letter' => 'Username must contain at least one letter',
      ],
      'surname' => [
        'required' => 'Lastname is required',
      ],
      'country' => [
        'required' => 'Country is required',
      ],
      'date_of_birth' => [
        'required' => 'Date of birth is required',
        'no_future_year' => 'Year cannot be in the future.',
        'no_past_year' => 'Year cannot be before 1910.',
      ],
      'gender' => [
        'required' => 'Gender is required',
      ],
      'user' => [
        'already_exists' => 'User already exists',
        'not_found' => 'User not found',
        'already_authenticated' => 'User already authenticated',
      ],
    ],

    'Success' => [
      'login' => 'You are logged in!',
      'reset_password' => 'Your password has been reset!',
      'verify_email' => 'A fresh verification link has been sent to your eMail address.',
      'password_reset_success' => 'Your password has been reset!',
      'email_verified' => 'Your eMail has been verified!',
    ],
  ],
  'Navigation' => [
    'home' => 'Home',
    'login' => 'Login',
    'signIn' => 'Sign in',
    'register' => 'Register',
    'reset_password' => 'Reset password',
    'verify_email' => 'Verify eMail',
    'profile' => 'Profile',
    'settings' => 'Settings',
    'logout' => 'Sign Out',
    'my_account' => 'My Account',
    'edit_profile' => 'Edit Profile',
    'back' => 'Back',
    'no_bookings' => 'No bookings found',
    'my_bookings' => 'My Bookings',
    'welcome' => 'Welcome',
  ],
  'Menu' => [
    'home' => 'Home',
    'booking' => 'Booking',
    'artist' => 'Artists',
    'ship' => 'The ship',
    'event' => 'The Event',
    'faq' => 'Faq',
    'more' => 'More',
    'check_booking' => 'Review Booking',
    'make_payment' => 'Make a Payment',
  ],
  'Footer' => [
    'slogan' => 'BECOME PART OF OUR INTERNATIONAL HEAVY METAL FAMILY!',
    'forum' => 'Official Forum',
    'newsletter' => 'Newsletter',
    'newsletter_subscribe' => 'Subscribe to our newsletter',
    'newsletter_thanks' => 'Thank you for subscribing to our newsletter!',
    'Event' => [
      'event' => 'THE EVENT',
      'artist' => 'Artists',
      'destination' => 'Our Destination',
      'miami' => 'Miami',
      'ship' => 'The Ship',
      'arrival' => 'Arrival & Departure',
    ],
    'Support' => [
      'support' => 'SUPPORT',
      'make_payment' => 'Make a Payment',
      'faq' => 'FAQ',
      'payment_schedule' => 'Payment Schedules',
      'travel_partners' => 'Travel Partners',
    ],
    'Legal' => [
      'legal' => 'LEGAL',
      'terms_cons' => 'Terms and Conditions',
      'age_require' => 'Age Requirements',
      'privacy_pol' => 'Privacy Policy',
    ],
    'Contact' => [
      'contact' => 'CONTACT US',
      'mail' => 'info@70000tons.com',
      'toll_free' => 'North America Toll-Free: 1-888-70K TONS (1-888-705-8667)',
      'other_areas' => 'All other areas: +1 305 777 4878',
    ],
  ],
];
