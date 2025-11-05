<?php
return [
  /*
    |--------------------------------------------------------------------------
    | System Emails Language Lines DE
    |--------------------------------------------------------------------------
    */
  'alerts' => [
    'sent' => 'E-Mail wurde erfolgreich versendet.',
    'send_failed' => 'E-Mail konnte nicht gesendet werden.',
    'already_verified' => 'E-Mail-Adresse wurde bereits bestätigt.',
    'verification_link_sent' => 'Bestätigungslink wurde versendet.',
    'not_found' => 'E-Mail-Adresse wurde nicht gefunden.',
  ],
  'common' => [
    'greeting' => [
      'hi' => 'Hi',
      'hello' => 'Hallo :name,',
      'default_name' => 'Kunde',
    ],
    'salutation' => [
      'thanks' => 'Cheers,',
      'regards' => 'Deine 70000TONS OF METAL Crew',
    ],
    'cta_help' => 'Falls du den Button nicht sehen kannst, klicke bitte auf den folgenden Link:',
  ],
  'account' => [
    'activation' => [
      'title' => 'Account erstellt',
      'welcome' => 'Wir freuen uns, dich an Bord willkommen zu heißen!',
      'created' => 'Dein Account wurde erfolgreich erstellt.',
      'activated' => 'Dein Account ist jetzt aktiv.',
      'survivor_number_label' => 'Deine Survivor-Nummer ist:',
      'cta_intro' => 'Bitte aktiviere deinen Account, indem du auf den Button klickst:',
      'cta_label' => 'Account aktivieren',
    ],
    'verification' => [
      'title' => 'Bestätige deine E-Mail-Adresse',
      'instruction' => 'Bitte klicke auf den Button, um deine E-Mail-Adresse zu bestätigen:',
      'fallback' => 'Wenn du keinen Account erstellt hast, musst du nichts weiter tun.',
      'cta_label' => 'E-Mail-Adresse bestätigen',
    ],
    'update' => [
      'subject' => 'Deine Accountinformationen wurden aktualisiert',
      'title' => 'E-Mail-Adresse aktualisiert',
      'greeting' => 'Hallo :name,',
      'default_name' => 'Kunde',
      'body' => 'Die E-Mail-Adresse deines Accounts wurde erfolgreich aktualisiert.',
      'security_notice' =>
        'Falls du diese Änderung nicht vorgenommen hast, kontaktiere bitte sofort unser Support-Team, um deinen Account zu sichern.',
      'signature' => '70000TONS OF METAL Crew',
    ],
  ],
  'invitation' => [
    'request' => [
      'title' => 'Anfrage zum Hinzufügen eines Gastes',
      'headline' => ':from lädt dich ein, die Kabine für :event zu teilen',
      'instructions' =>
        'Du hast 72 Stunden Zeit, deine Informationen in die Buchung einzutragen. Wenn du die Angaben nicht innerhalb dieses Zeitraums machst, wird die Anfrage storniert.',
      'account_notice' => 'Du hast eine Einladung in deinem Account.',
      'account_instructions' =>
        'Du hast 72 Stunden Zeit, diese Einladung anzunehmen. Wenn du sie nicht innerhalb dieses Zeitraums annimmst, wird sie storniert. Du findest deine Einladungen auf der Buchungsseite deines Accounts. Wenn du Fragen hast, kontaktiere bitte unsere Customer Service Crew.',
      'cta' => [
        'add_details' => 'Details hinzufügen',
        'complete_form' => 'Formular ausfüllen',
        'login' => 'In deinen Account einloggen',
      ],
      'booking_code_label' => 'Buchungscode',
    ],
  ],
  'password' => [
    'reset' => [
      'subject' => 'Du hast eine Passwortzurücksetzung angefordert',
      'title' => 'Passwort zurücksetzen',
      'intro' => 'Du erhältst diese E-Mail, weil wir eine Anfrage zum Zurücksetzen deines Passworts erhalten haben.',
      'cta_label' => 'Passwort zurücksetzen',
    ],
    'confirmation' => [
      'subject' => 'Dein Passwort wurde zurückgesetzt',
      'title' => 'Bestätigung der Passwortzurücksetzung',
      'intro' => 'Wir möchten dich informieren, dass dein Passwort erfolgreich zurückgesetzt wurde.',
    ],
  ],
  'seat' => [
    'reset' => [
      'subject' => 'Du wurdest aus deiner Buchung entfernt',
      'intro' => 'Wir möchten dich darüber informieren, dass du aus deiner Buchung entfernt wurdest.',
      'questions' =>
        'Falls du Fragen hast, wende dich bitte für weitere Informationen an den Lead Passenger deiner Kabine.',
    ],
  ],
];
