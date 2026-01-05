<?php
return [
    /*
    |--------------------------------------------------------------------------
    | System Emails Language Lines EN
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'sent' => 'Email sent successfully.',
        'send_failed' => 'Email not sent.',
        'already_verified' => 'Email already verified.',
        'verification_link_sent' => 'Verification link sent.',
        'not_found' => 'Email not found.',
    ],
    'common' => [
        'greeting' => [
            'hi' => 'Hi',
            'hello' => 'Hello :name,',
            'default_name' => 'Customer',
        ],
        'salutation' => [
            'thanks' => 'Cheers,',
            'regards' => 'your 70000TONS OF METAL crew',
        ],
        'cta_help' => 'If you can\'t see the button, please click the link below:',
    ],
    'account' => [
        'activation' => [
            'title' => 'Account created',
            'welcome' => 'We are excited to welcome you on board!',
            'created' => 'Your account has been created successfully.',
            'activated' => 'Your account is now active.',
            'survivor_number_label' => 'Your Survivor Number is:',
            'cta_intro' => 'Please activate your account by clicking the button below:',
            'cta_label' => 'Activate Account',
        ],
        'verification' => [
            'title' => 'Verify Your Email Address',
            'instruction' => 'Please click the button below to verify your email address:',
            'fallback' => 'If you did not create an account, no further action is required.',
            'cta_label' => 'Verify Email Address',
        ],
        'update' => [
            'subject' => 'Your account information has been updated',
            'title' => 'Email Address Updated',
            'greeting' => 'Hello :name,',
            'default_name' => 'Customer',
            'body' => 'Your account\'s email address has been successfully updated.',
            'security_notice' =>
                'If you did not make this change, please contact our support team immediately to secure your account.',
            'signature' => '70000TONS OF METAL Crew',
        ],
    ],
    'invitation' => [
        'request' => [
            'title' => 'Add Passenger Request',
            'headline' => ':from invites you to join their cabin for :event',
            'instructions' =>
                'You have 72 hours to enter your information into the booking. If you do not provide the details within this time, the request will be canceled.',
            'account_notice' => 'You have an invitation on your account.',
            'account_instructions' =>
                'You have 72 hours to accept this invitation. If you do not accept this invitation within this time, the invitation will be cancelled. You can check your invitations in your Bookings page on your account. If you have any questions, please contact our Customer Service Crew.',
            'cta' => [
                'add_details' => 'Add details',
                'complete_form' => 'Complete the form',
                'login' => 'Log in to your account',
            ],
            'booking_code_label' => 'Booking Code',
        ],
    ],
    'password' => [
        'reset' => [
            'subject' => 'You requested a password reset',
            'title' => 'Reset Password',
            'intro' => 'You are receiving this eMail because we received a password reset request for your account.',
            'cta_label' => 'Reset Password',
        ],
        'confirmation' => [
            'subject' => 'Your password has been reset',
            'title' => 'Password Reset Confirmation',
            'intro' => 'We would like to inform you that your password has been successfully reset.',
        ],
    ],
    'seat' => [
        'reset' => [
            'subject' => 'You have been removed from your booking',
            'intro' => 'We wanted to let you know that you have been removed from your booking.',
            'questions' =>
                'If you have any questions, please reach out to the Lead Passenger of your cabin for more information.',
        ],
    ],
    'payment' => [
        'received' => [
            'subject' => 'We Have Received Your Payment for 70000TONS OF METAL',
            'body' =>
                'We have received your payment of <strong>:paymentAmount</strong> for your booking with the code <strong>:bookingCode</strong>.',
            'next_steps' =>
                'You will receive an updated Booking Confirmation eMail once your payment has been fully processed.',
            'questions' =>
                'If you have any questions or need further assistance, please do not hesitate to contact us.',
        ],
    ],
];
