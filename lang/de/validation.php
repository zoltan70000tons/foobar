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

  'accepted' => ':attribute field muss akzeptiert werden.',
  'accepted_if' => ':attribute field muss akzeptiert werden, wenn :other :value ist.',
  'active_url' => ':attribute field muss eine gültige URL sein.',
  'after' => ':attribute field muss ein Datum nach :date sein.',
  'after_or_equal' => ':attribute field muss ein Datum nach oder am :date sein.',
  'alpha' => ':attribute field darf nur Buchstaben enthalten.',
  'alpha_dash' => ':attribute field darf nur Buchstaben, Nummern, Bindestriche und Unterstriche enthalten.',
  'alpha_num' => ':attribute field darf nur Buchstaben und Nummern enthalten.',
  'array' => ':attribute field muss eine Anordnung sein.',
  'ascii' => ':attribute field darf nur single-byte alphanumerische Zeichen und Symbole enthalten.',
  'before' => ':attribute field muss ein Datum vor :date sein.',
  'before_or_equal' => ':attribute field muss ein Datum vor oder am :date sein.',
  'between' => [
    'array' => ':attribute field muss zwischen :min und :max Zeichen haben.',
    'file' => ':attribute field muss zwischen :min und :max Kilobits haben.',
    'numeric' => ':attribute field muss zwischen :min und :max sein.',
    'string' => ':attribute field muss zwischen :min und :max Zeichen haben.',
  ],
  'boolean' => ':attribute field muss wahr oder falsch sein.',
  'can' => ':attribute field enthält einen unauthorisierten Wert.',
  'confirmed' => ':attribute field stimmt nicht mit der Bestätigung überein.',
  'contains' => ':attribute field fehlt ein benötigter Wert.',
  'current_password' => 'Das Passwort ist falsch.',
  'date' => ':attribute field muss ein gültiges Datum sein.',
  'date_equals' => ':attribute field muss ein Datum vor oder am :date sein.',
  'date_format' => ':attribute field muss dem Format :format entsprechen.',
  'decimal' => ':attribute field muss :decimal Dezimalstellen haben.',
  'declined' => ':attribute field muss verneint werden.',
  'declined_if' => ':attribute field muss verneint werden, wenn :value :other ist.',
  'different' => ':attribute field und :other müssen verschieden sein.',
  'digits' => ':attribute field muss :digits Ziffern haben.',
  'digits_between' => ':attribute field muss zwischen :min und :max Ziffern haben.',
  'dimensions' => ':attribute field hat ungültige Bilddimensionen.',
  'distinct' => ':attribute field hat einen doppelten Wert.',
  'doesnt_end_with' => ':attribute field darf nicht mit dem folgenden enden: :values:',
  'doesnt_start_with' => ':attribute field darf nicht mit dem folgenden starten: :values.',
  'email' => ':attribute field muss eine gültige eMailadresse sein.',
  'ends_with' => ':attribute field muss mit einem/r (depends on what follows) der folgenden enden: :values.',
  'enum' => 'Das gewählte :attribute ist ungültig.',
  'exists' => 'Das gewählte :attribute ist ungültig.',
  'extensions' => ':attribute field muss eine der folgenden Erweiterungen haben: :values.',
  'file' => ':attribute field muss eine Datei sein.',
  'filled' => ':attribute field muss mehr als :value Zeichen haben.',
  'gt' => [
    'array' => ':attribute field muss größer als :value Kilobit sein.',
    'file' => ':attribute field muss größer als :value sein.',
    'numeric' => ':attribute field muss größer als :value Zeichen sein.',
    'string' => ':attribute field muss :value oder mehr Zeichen haben.',
  ],
  'gte' => [
    'array' => ':attribute field muss größer oder gleich :value Kilobit haben.',
    'file' => ':attribute field muss größer oder gleich :value sein.',
    'numeric' => ':attribute field muss größer oder gleich :value Zeichen haben.',
    'string' => ':attribute field muss eine gültige hexadezimale Farbe sein.',
  ],
  'hex_color' => ':attribute field muss ein Bild sein.',
  'image' => 'Das gewählte :attribute ist ungültig.',
  'in' => ':attribute field muss in :other existieren.',
  'in_array' => ':attribute field muss eine ganze Zahl sein.',
  'integer' => ':attribute field muss eine gültige IP-Adresse sein.',
  'ip' => ':attribute field muss eine gültige IPv4-Adresse sein.',
  'ipv4' => 'Das :attribute Feld muss eine gültige IPv6-Adresse sein.',
  'ipv6' => ':attribute field muss eine gültige JSON-Kette sein.',
  'json' => ':attribute field muss eine Liste sein.',
  'list' => ':attribute field muss kleingeschrieben sein.',
  'lowercase' => ':attribute field muss weniger als :value Zeichen haben.',
  'lt' => [
    'array' => ':attribute field muss weniger als :value Kilobit haben.',
    'file' => ':attribute field muss weniger als :value sein.',
    'numeric' => ':attribute field muss weniger als :value Zeichen haben.',
    'string' => ':attribute field darf nicht mehr als :value Zeichen haben.',
  ],
  'lte' => [
    'array' => ':attribute field muss weniger als oder gleich :value Kilobit haben.',
    'file' => ':attribute field muss weniger oder gleich :value sein.',
    'numeric' => ':attribute field muss weniger als gleich :value Zeichen haben.',
    'string' => ':attribute field muss eine gültige MAC-Adresse sein.',
  ],
  'mac_address' => ':attribute field darf nicht mehr als :max Zeichen haben.',
  'max' => [
    'array' => ':attribute field darf nicht größer als :max Kilobit sein.',
    'file' => ':attribute field darf nicht größer als :max sein.',
    'numeric' => ':attribute field darf nicht größer als :max Zeichen sein.',
    'string' => ':attribute field darf nicht mehr als :max Ziffern haben.',
  ],
  'max_digits' => ':attribute field muss eine Datei des Typs :value sein',
  'mimes' => ':attribute field muss eine Datei des Typs :value sein',
  'mimetypes' => ':attribute field muss mindestens :min Zeichen haben.',
  'min' => [
    'array' => ':attribute field muss mindestens :min Kilobit haben.',
    'file' => ':attribute field muss mindestens :min sein.',
    'numeric' => ':attribute field muss mindestens :min Zeichen haben.',
    'string' => ':attribute field muss :min Ziffern haben.',
  ],
  'min_digits' => ':attribute field muss fehlen.',
  'missing' => ':attribute field muss fehlen, wenn :other :value ist.',
  'missing_if' => ':attribute field muss fehlen, es sei denn :other ist :value.',
  'missing_unless' => ':attribute field muss fehlen, es sei denn :value existiert.',
  'missing_with' => ':attribute field muss fehlen wenn :values existieren.',
  'missing_with_all' => ':attribute field muss eine Variable von :value sein.',
  'multiple_of' => 'Das gewählte :attribute ist ungültig.',
  'not_in' => 'Das gewählte :attribute ist ungültig.',
  'not_regex' => 'Das Format von :attribute field ist ungültig.',
  'numeric' => ':attribute field muss eine Zahl sein.',
  'password' => [
    'letters' => ':attribute field muss mindestens einen Buchstaben enthalten.',
    'mixed' => ':attribute field muss mindestens einen Groß- und einen Kleinbuchstaben enthalten.',
    'numbers' => ':attribute field muss mindestens eine Zahl enthalten.',
    'symbols' => ':attribute field muss mindestens ein Sonderzeichen enthalten.',
    'uncompromised' => 'Das :attribute field ist in einem Datenleck aufgetaucht. Bitte wähle ein anderes :attribute.',
  ],
  'present' => ':attribute field muss existieren.',
  'present_if' => ':attribute field muss existieren, wenn :other :value ist.',
  'present_unless' => ':attribute field muss existieren, es sei denn :other ist :value.',
  'present_with' => ':attribute field muss existieren, wenn :value existiert.',
  'present_with_all' => ':attribute field muss existieren, wenn :values existieren.',
  'prohibited' => ':attribute field ist verboten.',
  'prohibited_if' => ':attribute field ist verboten, wenn :other :value ist.',
  'prohibited_unless' => ':attribute field ist verboten, es sei denn :other ist innerhalb von :values.',
  'prohibits' => ':attribute field verbietet, dass :other präsent ist.',
  'regex' => ':attribute field Format ist ungültig.',
  'required' => ':attribute field wird benötigt.',
  'required_array_keys' => ':attribute field muss Einträge enthalten für: :values.',
  'required_if' => ':attribute field wird benötigt, wenn :other :value ist.',
  'required_if_accepted' => ':attribute field wird benötigt, wenn :other akzeptiert wird.',
  'required_if_declined' => ':attribute field wird benötigt, wenn :other verneint wird.',
  'required_unless' => ':attribute field wird benötigt, wenn :other in :values enthalten ist.',
  'required_with' => ':attribute field wird benötigt, wenn :values vorhanden sind.',
  'required_with_all' => ':attribute field wird benötigt, wenn :values vorhanden ist.',
  'required_without' => ':attribute field wird benötigt, wenn :values nicht existiert.',
  'required_without_all' => ':attribute field wird benötigt, wenn nichts von :values vorhanden sind.',
  'same' => ':attribute field muss mit :other übereinstimmen.',
  'size' => [
    'array' => ':attribute field muss :size Zeichen enthalten',
    'file' => ':attribute field muss :size Kilobit sein.',
    'numeric' => ':attribute field muss :size sein.',
    'string' => ':attribute field muss :size Zeichen sein.',
  ],
  'starts_with' => ':attribute field muss mit einer/m (depends on what follows) der folgenden beginnen: :values.',
  'string' => ':attribute field muss eine Kette sein.',
  'timezone' => ':attribute field muss eine gültige Zeitzone sein.',
  'unique' => 'Diese :attribute ist bereits vergeben.',
  'uploaded' => ':attribute konnte nicht geladen werden.',
  'uppercase' => ':attribute muss groß geschrieben sein.',
  'url' => ':attribute field muss eine gültige URL sein.',
  'ulid' => ':attribute field muss eine gültige ULID sein.',
  'uuid' => ':attribute field muss eine gültige UUID sein.',

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
        'rule-name' => 'Individuelle Nachricht',
      ],
    ],
    'unique_activated_email' => 'Diese eMailadresse ist bereits vergeben.',

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
