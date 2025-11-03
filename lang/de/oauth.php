<?php

return [
  /*
    |--------------------------------------------------------------------------
    | OAuth Language Lines DE
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during OAuth for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

  'Auth' => [
    'login_title' => 'Anmelden',
    'register_title' => 'Registrieren',
    'account_credentials_title' => 'Anmeldedaten',
    'personal_information_title' => 'Persönliche Daten',
    'reset_password_title' => 'Passwort zurücksetzen',
    'verify_email_title' => 'eMailadresse',
    'login' => 'Anmelden',
    'signIn' => 'Anmelden',
    'identifier' => 'eMailadresse oder Survivornummer',
    'recover_account_instructions' => 'Bist Du schon mit uns gereist? Verlinke Deine eMailadresse oder Survivornummer!',
    'account_recovered' =>
      'Deine Survivornummer wurde erfolgreich mit Deiner eMailadresse verknüpft. Bitte überprüfe Deinen Posteingang, um den Vorgang abzuschließen.',
    'logout' => 'Abmelden',
    'register' => 'Registrieren',
    'survivor_number' => 'Survivornummer',
    'email' => 'eMailadresse',
    'confirm_email' => 'eMailadresse bestätigen',
    'country' => 'Land',
    'loginId' => 'Deine eMailadresse',
    'password' => 'Passwort',
    'password_confirmation' => 'Passwort bestätigen',
    'old_password' => 'Altes Passwort',
    'new_password' => 'Neues Passwort',
    'confirm_new_password' => 'Neues Passwort bestätigen',
    'remember_me' => 'Erinnere Dich an mich',
    'forgot_password' => 'Passwort vergessen?',
    'name' => 'Vorname',
    'middle_name' => 'Zweitname',
    'surname' => 'Nachname',
    'gender' => 'Geschlecht',
    'dob' => 'Geburtsdatum',
    'month' => 'Monat',
    'day' => 'Tag',
    'year' => 'Jahr',
    'address' => 'Adresse',
    'address1' => 'Adresszeile 1',
    'address2' => 'Adresszeile 2',
    'city' => 'Stadt',
    'state' => 'Bundesland',
    'zip_code' => 'Postleitzahl',
    'reset_password' => 'Passwort zurücksetzen',
    'send_password_reset_link' => 'Sende einen Link, um das Passwort zurückzusetzen.',
    'send_email' => 'eMail senden',
    'confirm_password' => 'Passwort bestätigen',
    'verify_email' => 'eMailadresse bestätigen',
    'verify_email_sent' => 'Ein neuer Verifizierungslink wurde an Deine eMailadresse verschickt.',
    'check_email' =>
      'Fast fertig! Bevor es weitergeht, gucke bitte in Deinem eMailpostfach nach dem Verifizierungslink. Solltest Du ihn nicht finden, gucke bitte in Deinem Spamordner nach.',
    'receive_email' => 'Wenn Du die Nachricht nicht erhalten hast, klicke bitte hier, um eine neue anzufordern.',
    'link_expired' => 'Dein Verifizierungslink ist abgelaufen.',
    'account_terms' =>
      'Ich stimme den <terms>Vertragsbedingungen und Konditionen</terms>  und <privacy>Datenschutzrichtlinien</privacy> zu.',
    'marketing' => 'Ich bin damit einverstanden, eMails zu Werbezwecken zu bekommen.',
    'dont_have_account' => 'Du hast keinen Account?',
    'have_account' => 'Du hast bereits einen Account?',
    'login_to_your' =>
      'Bitte verwende Deine eMailadresse oder Deine Survivornummer, um Dich bei Deinem Account anzumelden.',
    'phone' => 'Telefon',
    'citizenship' => 'Staatsbürgerschaft',
    'emergency_contact' => 'Name des Notfallkontakts',
    'emergency_phone' => 'Telefonnummer des Notfallkontakts',
    'link_sent' => 'Der Link wurde erfolgreich versendet',
    'password_helper' => 'Das Passwort muss mindestens 8 Zeichen lang sein und ein Sonderzeichen beinhalten:',
    'min_length' => 'Mindestlänge',
    'special_chars' => 'Enthält ein Sonderzeichen',

    'UpdateEmail' => [
      'title' => 'Ändere Deine übliche eMailadresse',
      'disclaimer' =>
        'Bitte bedenke, dass Deine übliche eMailadresse die ist, mit der Du Dich bei Deinem Account anmeldest. Sie wird auch die eMailadresse für die Buchungsplattform sein.',
    ],

    'Error' => [
      'general' => [
        'special' => 'Sonderzeichen sind nicht zugelassen.',
      ],
      'invalid_session_request' =>
        'Ungültige Anfrage oder Zeit abgelaufen. Bitte aktualisiere die Seite und versuche es erneut.',
      'identifier' => [
        'required' => 'eMailadresse oder Survivornummer wird benötigt. ',
        'email' => 'Die eMailadresse muss eine gültige eMailadresse sein.',
        'invalid' => 'Ungültige Anmeldedaten',
      ],
      'email' => [
        'required' => 'eMailadresse wird benötigt.',
        'email' => 'Die eMailadresse muss eine gültige eMailadresse sein.',
        'invalid' => 'Ungültiges Passwort oder eMailadresse',
      ],
      'survivor_number' => [
        'required' => 'Eine Survivornummer wird benötigt',
        'min' => 'Die Survivornummer muss mindestens 9 Ziffern haben',
        'invalid' => 'Die Survivornummer muss ein neunstelliger numerischer Code sein.',
      ],
      'password' => [
        'required' => 'Passwort wird benötigt',
        'min' => 'Das Passwort muss mindestens 8 Zeichen enthalten.',
        'special_char' => 'Das Passwort muss mindestens ein Sonderzeichen enthalten.',
      ],
      'privacy_policy' => [
        'required' => 'Ich akzeptiere die Vertragsbedinungen und Konditionen, sowie die Datenschutzrichtlinien.',
      ],
      'password_confirmation' => [
        'required' => 'Das Passwort muss bestätigt werden.',
        'same' => 'Die Bestätigung des Passwortes und das Passwort müssen übereinstimmen.',
      ],
      'name' => [
        'required' => 'Vorname wird benötigt.',
        'invalid' => 'Benutzername kann keine Sonderzeichen enthalten.',
        'letter' => 'Benutzername muss mindestens einen Buchstaben enthalten.',
      ],
      'surname' => [
        'required' => 'Nachname wird benötigt.',
      ],
      'country' => [
        'required' => 'Land wird benötigt',
      ],
      'date_of_birth' => [
        'required' => 'Geburtsdatum wird benötigt',
        'no_future_year' => 'Das Jahr kann nicht in der Zukunft liegen',
        'no_past_year' => 'Das Jahr kann nicht vor 1910 liegen',
      ],
      'gender' => [
        'required' => 'Geschlecht wird benötigt',
      ],
      'user' => [
        'already_exists' => 'Benutzer existiert bereits',
        'not_found' => 'Benutzer nicht gefunden',
        'already_authenticated' => 'Benutzer wurde bereits authentifiziert',
      ],
    ],

    'Success' => [
      'login' => 'Du bist angemeldet! ',
      'reset_password' => 'Dein Passwort wurde zurückgesetzt! ',
      'verify_email' => 'Ein neuer Verifizierungslink wurde an Deine eMailadresse geschickt.',
      'password_reset_success' => 'Dein Passwort wurde zurückgesetzt! ',
      'email_verified' => 'Deine eMailadresse wurde bestätigt!',
    ],
  ],
  'Navigation' => [
    'home' => 'Start',
    'login' => 'Log-In',
    'signIn' => 'Anmelden',
    'register' => 'Registrieren',
    'reset_password' => 'Passwort zurücksetzen',
    'verify_email' => 'eMailadresse bestätigen',
    'profile' => 'Profil',
    'settings' => 'Einstellungen',
    'logout' => 'Abmelden',
    'my_account' => 'Mein Benutzerkonto',
    'edit_profile' => 'Benutzerkonto bearbeiten',
    'back' => 'Zurück',
    'no_bookings' => 'Keine Buchungen gefunden',
    'my_bookings' => 'Meine Buchungen',
    'welcome' => 'Willkommen',
  ],
  'Menu' => [
    'home' => 'Startseite',
    'booking' => 'Buchung',
    'artist' => 'BANDS',
    'ship' => 'Das Schiff',
    'event' => 'Das Event',
    'faq' => 'FAQ',
    'more' => 'Mehr',
    'check_booking' => 'Buchung prüfen',
    'make_payment' => 'Bezahlen',
  ],
  'Footer' => [
    'slogan' => 'WERDE TEIL UNSERER INTERNATIONALEN HEAVY-METAL-FAMILIE!',
    'forum' => 'Offizielles Forum',
    'newsletter' => 'Newsletter',
    'newsletter_subscribe' => 'Abonniere unseren Newsletter',
    'newsletter_thanks' => 'Danke, dass Du unseren Newsletter abonniert hast!',
    'Event' => [
      'event' => 'DAS EVENT',
      'artist' => 'Künstler',
      'destination' => 'Unser Ziel',
      'miami' => 'Miami',
      'ship' => 'Das Schiff',
      'arrival' => 'Ankunft und Abreise',
    ],
    'Support' => [
      'support' => 'SUPPORT',
      'make_payment' => 'Eine Zahlung machen',
      'faq' => 'Fragen und Antworten',
      'payment_schedule' => 'Zeitplan für Ratenzahlung',
      'travel_partners' => 'Reisepartner',
    ],
    'Legal' => [
      'legal' => 'RECHTLICHES',
      'terms_cons' => 'Vertragsbedingungen und Konditionen',
      'age_require' => 'Altersvoraussetzungen',
      'privacy_pol' => 'Datenschutzrichtlinie',
    ],
    'Contact' => [
      'contact' => 'KONTAKTIERE UNS ',
      'mail' => 'info@70000tons.com',
      'toll_free' => 'Nordamerika gebührenfrei: 1-888-70K TONS (1-888-705-8667)',
      'other_areas' => 'Alle anderen Regionen: +1 305 777 4878',
    ],
  ],
];
