<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

  'accepted' => 'Das :attribute muss akzeptiert werden.',
  'accepted_if' => 'Das :attribute muss akzeptiert werden, wenn :other :value ist.',
  'active_url' => 'Das :attribute ist keine gültige URL.',
  'after' => 'Das :attribute muss ein Datum nach :date sein.',
  'after_or_equal' => 'Das :attribute muss ein Datum nach oder gleich :date sein.',
  'alpha' => 'Das :attribute darf nur Buchstaben enthalten.',
  'alpha_dash' => 'Das :attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
  'alpha_num' => 'Das :attribute darf nur Buchstaben und Zahlen enthalten.',
  'array' => 'Das :attribute muss ein Array sein.',
  'ascii' => 'Das :attribute darf nur einbyteige alphanumerische Zeichen und Symbole enthalten.',
  'before' => 'Das :attribute muss ein Datum vor :date sein.',
  'before_or_equal' => 'Das :attribute muss ein Datum vor oder gleich :date sein.',
  'between' => [
    'array' => 'Das :attribute muss zwischen :min und :max Elemente haben.',
    'file' => 'Das :attribute muss zwischen :min und :max Kilobyte groß sein.',
    'numeric' => 'Das :attribute muss zwischen :min und :max liegen.',
    'string' => 'Das :attribute muss zwischen :min und :max Zeichen lang sein.',
  ],
  'boolean' => 'Das :attribute Feld muss wahr oder falsch sein.',
  'can' => 'Das :attribute enthält einen unzulässigen Wert.',
  'confirmed' => 'Die :attribute Bestätigung stimmt nicht überein.',
  'contains' => 'Das :attribute Feld fehlt ein erforderlicher Wert.',
  'current_password' => 'Das Passwort ist falsch.',
  'date' => 'Das :attribute ist kein gültiges Datum.',
  'date_equals' => 'Das :attribute muss ein Datum gleich :date sein.',
  'date_format' => 'Das :attribute entspricht nicht dem Format :format.',
  'decimal' => 'Das :attribute muss :decimal Dezimalstellen haben.',
  'declined' => 'Das :attribute muss abgelehnt werden.',
  'declined_if' => 'Das :attribute muss abgelehnt werden, wenn :other :value ist.',
  'different' => 'Das :attribute und :other müssen unterschiedlich sein.',
  'digits' => 'Das :attribute muss :digits Stellen haben.',
  'digits_between' => 'Das :attribute muss zwischen :min und :max Stellen haben.',
  'dimensions' => 'Das :attribute hat ungültige Bildabmessungen.',
  'distinct' => 'Das :attribute Feld hat einen doppelten Wert.',
  'doesnt_end_with' => 'Das :attribute darf nicht mit einem der folgenden enden: :values.',
  'doesnt_start_with' => 'Das :attribute darf nicht mit einem der folgenden beginnen: :values.',
  'email' => 'Das :attribute muss eine gültige E-Mail-Adresse sein.',
  'ends_with' => 'Das :attribute muss mit einem der folgenden enden: :values.',
  'enum' => 'Das gewählte :attribute ist ungültig.',
  'exists' => 'Das gewählte :attribute ist ungültig.',
  'extensions' => 'Das :attribute muss eine der folgenden Dateierweiterungen haben: :values.',
  'file' => 'Das :attribute muss eine Datei sein.',
  'filled' => 'Das :attribute muss einen Wert haben.',
  'gt' => [
    'array' => 'Das :attribute muss mehr als :value Elemente haben.',
    'file' => 'Das :attribute muss größer als :value Kilobyte sein.',
    'numeric' => 'Das :attribute muss größer als :value sein.',
    'string' => 'Das :attribute muss größer als :value Zeichen sein.',
  ],
  'gte' => [
    'array' => 'Das :attribute muss :value Elemente oder mehr haben.',
    'file' => 'Das :attribute muss größer oder gleich :value Kilobyte sein.',
    'numeric' => 'Das :attribute muss größer oder gleich :value sein.',
    'string' => 'Das :attribute muss größer oder gleich :value Zeichen sein.',
  ],
  'hex_color' => 'Das :attribute muss eine gültige Hexadezimalfarbe sein.',
  'image' => 'Das :attribute muss ein Bild sein.',
  'in' => 'Das gewählte :attribute ist ungültig.',
  'in_array' => 'Das :attribute Feld existiert nicht in :other.',
  'integer' => 'Das :attribute muss eine Ganzzahl sein.',
  'ip' => 'Das :attribute muss eine gültige IP-Adresse sein.',
  'ipv4' => 'Das :attribute muss eine gültige IPv4-Adresse sein.',
  'ipv6' => 'Das :attribute muss eine gültige IPv6-Adresse sein.',
  'json' => 'Das :attribute muss ein gültiger JSON-String sein.',
  'list' => 'Das :attribute muss eine Liste sein.',
  'lowercase' => 'Das :attribute muss in Kleinbuchstaben geschrieben sein.',
  'lt' => [
    'array' => 'Das :attribute muss weniger als :value Elemente haben.',
    'file' => 'Das :attribute muss kleiner als :value Kilobyte sein.',
    'numeric' => 'Das :attribute muss kleiner als :value sein.',
    'string' => 'Das :attribute muss kleiner als :value Zeichen sein.',
  ],
  'lte' => [
    'array' => 'Das :attribute darf nicht mehr als :value Elemente haben.',
    'file' => 'Das :attribute muss kleiner oder gleich :value Kilobyte sein.',
    'numeric' => 'Das :attribute muss kleiner oder gleich :value sein.',
    'string' => 'Das :attribute muss kleiner oder gleich :value Zeichen sein.',
  ],
  'mac_address' => 'Das :attribute muss eine gültige MAC-Adresse sein.',
  'max' => [
    'array' => 'Das :attribute darf nicht mehr als :max Elemente haben.',
    'file' => 'Das :attribute darf nicht größer als :max Kilobyte sein.',
    'numeric' => 'Das :attribute darf nicht größer als :max sein.',
    'string' => 'Das :attribute darf nicht größer als :max Zeichen sein.',
  ],
  'max_digits' => 'Das :attribute darf nicht mehr als :max Stellen haben.',
  'mimes' => 'Das :attribute muss eine Datei des Typs: :values sein.',
  'mimetypes' => 'Das :attribute muss eine Datei des Typs: :values sein.',
  'min' => [
    'array' => 'Das :attribute muss mindestens :min Elemente haben.',
    'file' => 'Das :attribute muss mindestens :min Kilobyte groß sein.',
    'numeric' => 'Das :attribute muss mindestens :min sein.',
    'string' => 'Das :attribute muss mindestens :min Zeichen lang sein.',
  ],
  'min_digits' => 'Das :attribute muss mindestens :min Stellen haben.',
  'missing' => 'Das :attribute muss fehlen.',
  'missing_if' => 'Das :attribute muss fehlen, wenn :other :value ist.',
  'missing_unless' => 'Das :attribute muss fehlen, außer :other ist :value.',
  'missing_with' => 'Das :attribute muss fehlen, wenn :values vorhanden ist.',
  'missing_with_all' => 'Das :attribute muss fehlen, wenn :values vorhanden sind.',
  'multiple_of' => 'Das :attribute muss ein Vielfaches von :value sein.',
  'not_in' => 'Das gewählte :attribute ist ungültig.',
  'not_regex' => 'Das Format von :attribute ist ungültig.',
  'numeric' => 'Das :attribute muss eine Zahl sein.',
  'password' => [
    'letters' => 'Das :attribute muss mindestens einen Buchstaben enthalten.',
    'mixed' => 'Das :attribute muss mindestens einen Groß- und einen Kleinbuchstaben enthalten.',
    'numbers' => 'Das :attribute muss mindestens eine Zahl enthalten.',
    'symbols' => 'Das :attribute muss mindestens ein Symbol enthalten.',
    'uncompromised' => 'Das :attribute ist in einem Datenleck aufgetaucht. Bitte wählen Sie ein anderes :attribute.',
  ],
  'present' => 'Das :attribute muss vorhanden sein.',
  'present_if' => 'Das :attribute muss vorhanden sein, wenn :other :value ist.',
  'present_unless' => 'Das :attribute muss vorhanden sein, außer :other ist :value.',
  'present_with' => 'Das :attribute muss vorhanden sein, wenn :values vorhanden ist.',
  'present_with_all' => 'Das :attribute muss vorhanden sein, wenn :values vorhanden sind.',
  'prohibited' => 'Das :attribute ist verboten.',
  'prohibited_if' => 'Das :attribute ist verboten, wenn :other :value ist.',
  'prohibited_unless' => 'Das :attribute ist verboten, außer :other ist in :values.',
  'prohibits' => 'Das :attribute verbietet die Anwesenheit von :other.',
  'regex' => 'Das Format von :attribute ist ungültig.',
  'required' => 'Das :attribute ist erforderlich.',
  'required_array_keys' => 'Das :attribute muss Einträge für: :values enthalten.',
  'required_if' => 'Das :attribute ist erforderlich, wenn :other :value ist.',
  'required_if_accepted' => 'Das :attribute ist erforderlich, wenn :other akzeptiert wird.',
  'required_if_declined' => 'Das :attribute ist erforderlich, wenn :other abgelehnt wird.',
  'required_unless' => 'Das :attribute ist erforderlich, außer :other ist in :values.',
  'required_with' => 'Das :attribute ist erforderlich, wenn :values vorhanden ist.',
  'required_with_all' => 'Das :attribute ist erforderlich, wenn :values vorhanden sind.',
  'required_without' => 'Das :attribute ist erforderlich, wenn :values nicht vorhanden ist.',
  'required_without_all' => 'Das :attribute ist erforderlich, wenn keiner der :values vorhanden ist.',
  'same' => 'Das :attribute und :other müssen übereinstimmen.',
  'size' => [
    'array' => 'Das :attribute muss :size Elemente enthalten.',
    'file' => 'Das :attribute muss :size Kilobyte groß sein.',
    'numeric' => 'Das :attribute muss :size groß sein.',
    'string' => 'Das :attribute muss :size Zeichen lang sein.',
  ],
  'starts_with' => 'Das :attribute muss mit einem der folgenden beginnen: :values.',
  'string' => 'Das :attribute muss eine Zeichenkette sein.',
  'timezone' => 'Das :attribute muss eine gültige Zeitzone sein.',
  'unique' => 'Das :attribute wurde bereits vergeben.',
  'uploaded' => 'Das Hochladen von :attribute ist fehlgeschlagen.',
  'uppercase' => 'Das :attribute muss in Großbuchstaben geschrieben sein.',
  'url' => 'Das :attribute muss eine gültige URL sein.',
  'ulid' => 'Das :attribute muss eine gültige ULID sein.',
  'uuid' => 'Das :attribute muss eine gültige UUID sein.',

  /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

  'custom' => [
    'attribute-name' => [
      'rule-name' => 'benutzerdefinierte Nachricht',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

  'attributes' => [],

];
