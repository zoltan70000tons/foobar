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

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'accepted_if' => 'El campo :attribute debe ser aceptado cuando :other sea :value.',
    'active_url' => 'El campo :attribute debe ser una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha después del :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha después o igual al :date.',
    'alpha' => 'El campo :attribute debe contener letras solamente.',
    'alpha_dash' => 'El campo :attribute debe contener solamente letras, números, guiones y guiones bajos.',
    'alpha_num' => 'El campo :attribute debe contener solamente letras y números.',
    'array' => 'El campo :attribute debe ser una lista.',
    'ascii' => 'El campo :attribute debe contener solamente caracteres alfanuméricos y símbolos de un solo byte.',
    'before' => 'El campo :attribute debe ser una fecha antes del :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha antes o igual al :date.',
    'between' => [
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file' => 'El campo :attribute debe de ser de entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe de ser entre :min y :max.',
        'string' => 'El campo :attribute debe de tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'can' => 'El campo :attribute contiene un valor no autorizado.',
    'confirmed' => 'La confirmación del campo :attribute no coincide.',
    'contains' => 'Al campo :attribute le falta un valor requerido.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'date_equals' => 'El campo :attribute debe ser una fecha igual al :date.',
    'date_format' => 'El campo :attribute debe de contener el formato :format.',
    'decimal' => 'El campo :attribute debe de tener :decimal lugares decimales.',
    'declined' => 'El campo :attribute debe de ser rechazado.',
    'declined_if' => 'El campo :attribute debe de ser rechazado cuando :other es :value.',
    'different' => 'El campo :attribute y :other deben de ser diferentes.',
    'digits' => 'El campo :attribute debe de tener :digits dígitos.',
    'digits_between' => 'El campo :attribute debe de tener entre :min y :max dígitos.',
    'dimensions' => 'El campo :attribute contiene dimensiones inválidas de imagen.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'doesnt_end_with' => 'El campo :attribute no debe terminar con alguno de los siguientes: :values.',
    'doesnt_start_with' => 'El campo :attribute no debe de empezar con alguno de los siguientes: :values.',
    'email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
    'ends_with' => 'El campo :attribute debe de terminar con alguno de los siguientes :values.',
    'enum' => 'El :attribute seleccionado es inválido.',
    'exists' => 'El :attribute seleccionado es inválido.',
    'extensions' => 'El campo :attribute debe de tener alguna de las siguientes extensiones: :values.',
    'file' => 'El campo :attribute debe de ser un archivo.',
    'filled' => 'El campo :attribute debe de tener más de :value elementos.',
    'gt' => [
        'array' => 'El campo :attribute debe de ser mayor a :value kilobytes.',
        'file' => 'El campo :attribute debe de ser mayor a :vaue.',
        'numeric' => 'El campo :attribute debe de tener más de :value caracteres.',
        'string' => 'El campo :attribute debe de tener :value o más elementos.',
    ],
    'gte' => [
        'array' => 'El campo :attribute debe  de ser igual o mayor a :value kilobytes.',
        'file' => 'El campo :attribute debe de ser igual o mayor a :value.',
        'numeric' => 'El campo :attribute debe de tener igual o más de :value caracteres.',
        'string' => 'El campo :attribute debe de tener un color hexadecimal válido.',
    ],
    'hex_color' => 'El campo :attribute debe de ser una imagen.',
    'image' => 'El :attribute seleccionado es inválido.',
    'in' => 'El campo :attribute debe de existir en :other.',
    'in_array' => 'El campo :attribute debe ser un número entero.',
    'integer' => 'El campo :attribute debe de ser una dirección de IP válida.',
    'ip' => 'El campo :attribute debe de ser una dirección de IPv4 válida.',
    'ipv4' => 'El campo :attribute debe de ser una dirección de IPv6 válida.',
    'ipv6' => 'El campo :attribute debe ser una cadena JSON válida.',
    'json' => 'El campo :attribute debe ser una lista.',
    'list' => 'El campo :attribute debe de estar en minúsculas',
    'lowercase' => 'El campo :attribute debe tener menos de :value elementos.',
    'lt' => [
        'array' => 'El campo :attribute debe ser menor a :value kilobytes',
        'file' => '"El campo :attribute debe ser menor a :value.',
        'numeric' => 'El campo :attribute debe tener menos de :value caracteres.',
        'string' => 'El campo :attribute no debe de tener más de :value elementos.',
    ],
    'lte' => [
        'array' => 'El campo :attribute debe ser menor o igual a :value kilobytes.',
        'file' => 'El campo :attribute debe ser menor o igual a :value.',
        'numeric' => 'El campo :attribute debe tener menos o igual a :value caracteres.',
        'string' => 'El campo :attribute debe ser una dirección MAC válida.',
    ],
    'mac_address' => 'El campo :attribute no debe de tener más de :max elementos.',
    'max' => [
        'array' => 'l campo :attribute no debe ser mayor a :max kilobytes.',
        'file' => 'El campo :attribute no debe de ser mayor a :max.',
        'numeric' => 'El campo :attribute no debe de tener más de :max caracteres.',
        'string' => 'El campo :attribute no debe de tener más de :max dígitos.',
    ],
    'max_digits' => 'El campo :attribute debe de ser un archivo del tipo: :values.',
    'mimes' => 'El campo :attribute debe de ser un archivo del tipo: :values.',
    'mimetypes' => 'El campo :attribute debe de tener al menos :min elementos.',
    'min' => [
        'array' => 'El campo :attribute debe tener al menos :min kilobytes.',
        'file' => 'El campo :attribute debe de ser de al menos :min.',
        'numeric' => 'El campo :attribute debe de tener al menos :min caracteres.',
        'string' => 'El campo :attribute debe de tener al menos :min dígitos.',
    ],
    'min_digits' => 'El campo :attribute debe estar ausente.',
    'missing' => 'El campo :attribute debe de estar ausente cuando :other sea :value.',
    'missing_if' => 'El campo :attribute debe de estar ausente a no ser que :other sea :value.',
    'missing_unless' => 'El campo :attribute debe de estar ausente cuando :values esté presente.',
    'missing_with' => 'El campo :attribute debe de estar ausente cuando :values estén presentes.',
    'missing_with_all' => 'El campo :attribute debe de ser un múltiplo de :value.',
    'multiple_of' => 'El :attribute seleccionado no es válido.',
    'not_in' => 'El :attribute seleccionado no es válido.',
    'not_regex' => 'El formato del campo :attribute es inválido.',
    'numeric' => 'El campo :attribute debe de ser un número.',
    'password' => [
        'letters' => 'El campo :attribute debe de contener al menos una letra.',
        'mixed' => 'El campo :attribute debe de contener al menos una letra mayúscula y una minúscula.',
        'numbers' => 'El campo :attribute debe de contener al menos un número.',
        'symbols' => 'El campo :attribute debe de contener al menos un símbolo.',
        'uncompromised' =>
            'El :attribute proporcionado ha aparecido en una filtración de datos. Favor de elegir un :attribute diferente.',
    ],
    'present' => 'El campo :attribute debe de estar presente.',
    'present_if' => 'El campo :attribute debe de estar presente cuando :other sea :value.',
    'present_unless' => 'El campo :attribute debe de estar presente a menos que :other sea :value.',
    'present_with' => 'El campo :attribute debe de estar presente cuando :values esté presente.',
    'present_with_all' => 'El campo :attribute debe de estar presente cuando :values estén presentes.',
    'prohibited' => 'El campo :attribute está prohibido.',
    'prohibited_if' => 'El campo :attribute está prohibido cuando :other sea :value.',
    'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other esté en :values.',
    'prohibits' => 'El campo :attribute prohíbe que :other esté presente.',
    'regex' => 'El formato del campo :attribute es inválido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_array_keys' => 'El campo :attribute debe de contener entradas para: :values.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_if_accepted' => 'Se requiere el campo :attribute cuando :other es aceptado.',
    'required_if_declined' => 'Se requiere el campo :attribute cuando :other es rechazado.',
    'required_unless' => 'El campo :attribute es obligatorio, a menos que :other esté en :values.',
    'required_with' => 'Se requiere del campo :attribute cuando :values está presente.',
    'required_with_all' => 'Se requiere del campo :attribute cuando los :values están presentes.',
    'required_without' => 'Se requiere del campo :attribute cuando :values no está presente.',
    'required_without_all' => 'Se requiere del campo :attribute cuando ninguno de los :values están presentes.',
    'same' => 'El campo :attribute debe ser de coincidir con :other.',
    'size' => [
        'array' => 'El campo :attribute debe de contener :size elementos.',
        'file' => 'El campo :attribute debe ser de :size kilobytes.',
        'numeric' => 'El campo :attribute debe de ser de :size.',
        'string' => 'El campo :attribute debe de tener :size caracteres.',
    ],
    'starts_with' => 'El campo :attribute debe de empezar con alguno de los siguientes: :values.',
    'string' => 'El campo :attribute debe de ser una cadena de texto.',
    'timezone' => 'El campo :attribute debe de ser una zona horaria válida.',
    'unique' => 'El :attribute ya está en uso.',
    'uploaded' => 'El :attribute no se pudo subir.',
    'uppercase' => 'El campo :attribute debe de estar en mayúsculas.',
    'url' => 'El campo :attribute debe de ser una URL válida.',
    'ulid' => 'El campo :attribute debe de ser una ULID válida.',
    'uuid' => 'El campo :attribute debe de ser una UUID válida.',

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
            'rule-name' => 'Mensaje personalizado',
        ],
    ],
    'unique_activated_email' => 'El correo electrónico ya ha sido utilizado.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],
];
