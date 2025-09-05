<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth Language Lines ES
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during OAuth for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'Auth' => [
        'login_title' => "Ingresar",
        'register_title' => "Registrarse",
        'account_credentials_title' => "Credenciales de la cuenta",
        'personal_information_title' => "Información personal",
        'reset_password_title' => "Restablecer contraseña",
        'verify_email_title' => "Verificar correo",
        'login' => "Ingresar",
        'signIn' => "Ingresar",
        'identifier' => "Correo electrónico o número de Survivor",
        'recover_account_instructions' => "¿Has navegado anteriormente con nostros? ¡Vincula tu correo a tu Número de Survivor!",
        'logout' => "Cerrar sesión",
        'register' => "Registrarse",
        'survivor_number' => "Número de Survivor",
        'email' => "Correo electrónico",
        'confirm_email' => "Confirmar correo",
        'country' => "País",
        'loginId' => "Tu correo electrónico",
        'password' => "Contraseña",
        'password_confirmation' => "Confirmar contraseña",
        'old_password' => "Contraseña anterior",
        'new_password' => "Nueva contraseña",
        'confirm_new_password' => "Confirmar nueva contraseña",
        'remember_me' => "Recuérdame",
        'forgot_password' => "¿Olvidaste tu contraseña?",
        'name' => "Primer nombre",
        'middle_name' => "Segundo nombre",
        'surname' => "Apellido",
        'gender' => "Género",
        'dob' => "Fecha de nacimiento",
        'month' => "Mes",
        'day' => "Día",
        'year' => "Año",
        'address' => "Dirección",
        'address1' => "Dirección línea 1",
        'address2' => "Dirección línea 2",
        'city' => "Ciudad",
        'state' => "Provincia/estado",
        'zip_code' => "Código Postal",
        'reset_password' => "Restablecer contraseña",
        'send_password_reset_link' => "Enviar enlace para restablecer tu contraseña",
        'send_email' => "Enviar correo electrónico",
        'confirm_password' => "Confirmar contraseña",
        'verify_email' => "Verificar correo electrónico",
        'verify_email_sent' => "Un nuevo correo de verificación ha sido enviado a tu dirección de correo electrónico.",
        'check_email' => "¡Ya casi! Antes de continuar, revisa tu correo electrónico para encontrar el enlace de verificación. Si no lo ves, por favor revisa tu carpeta de spam.",
        'receive_email' => "Si no recibiste el mensaje, haz clic aquí para enviarlo nuevamente.",
        'account_terms' => "Acepto los <terms>Términos y Condiciones</terms> y la <privacy>Política de Privacidad</privacy>.",
        'marketing' => "Acepto recibir correos de promociones y/o comunicaciones",
        'dont_have_account' => "¿No tienes una cuenta aún?",
        'have_account' => "¿Ya tienes una cuenta?",
        'login_to_your' => "Por favor ingresa to dirección de correo electrónico o tu Número de Survivor para ingresar a tu cuenta.",
        'phone' => "Teléfono",
        'citizenship' => "Nacionalidad",
        'emergency_contact' => "Nombre del Contacto de Emergencia",
        'emergency_phone' => "Número de teléfono del Contacto de Emergencia",
        'link_sent' => "Se ha enviado un enlace",
        'password_helper' => "Su contraseña debe tener al menos 8 caracteres e incluir un caracter especial como:",
        'min_length' => "Largo mínimo",
        'special_chars' => "Contiene caracteres especiales",

        'UpdateEmail' => [
            'title' => "Cambia tu dirección de correo electrónico predeterminada",
            'disclaimer' => "Por favor ten en cuenta que tu dirección de correo electrónico predeterminada es aquella que usarás para ingresar a tu cuenta. Y por defecto, será la dirección de correo electrónico que aparecerá en la Plataforma de Reservas.",
        ],

        'Error' => [
            'general' => [
                'special' => "No se permiten caracteres especiales.",
            ],
            'invalid_session_request' => "Solicitud inválida o el tiempo ha expirado. Por favor, actualiza la página e intenta nuevamente",
            'identifier' => [
                'required' => "Se requiere un correo electrónico o Número de Survivor",
                'email' => "Se requiere una dirección de correo electrónico válida",
                'invalid' => "Credenciales inválidas",
            ],
            'email' => [
                'required' => "Se require una dirección de correo electrónico",
                'email' => "Se requiere una dirección de correo electrónico válida",
                'invalid' => "Correo electrónico o contraseña inválidos",
            ],
            'survivor_number' => [
                'required' => "Se requiere un Número de Survivor",
                'min' => "El Número de Survivor debe contener al menos 9 caracteres",
                'invalid' => "El Número de Survivor debe contener un código de 9 dígitos.",
            ],
            'password' => [
                'required' => "Se requiere contraseña",
                'min' => "La contraseña debe contener al menos 8 caracteres",
                'special_char' => "La contraseña debe contener al menos un carácter especial",
            ],
            'privacy_policy' => [
                'required' => "Debes aceptar los Términos y Condiciones y la Política de Privacidad.",
            ],
            'password_confirmation' => [
                'required' => "Se requiere la confirmación de tu contraseña",
                'same' => "La confirmación de tu contraseña debe conicidir con tu contraseña",
            ],
            'name' => [
                'required' => "Se requiere el primer nombre",
                'invalid' => "El nombre de usuario no puede contener caracteres especiales",
                'letter' => "El nombre de usuario debe contener al menos una letra",
            ],
            'surname' => [
                'required' => "Se requiere el apellido",
            ],
            'country' => [
                'required' => "Se requiere el país",
            ],
            'date_of_birth' => [
                'required' => "Se requiere la fecha de nacimiento",
                'no_future_year' => "El año no puede estar en el futuro.",
                'no_past_year' => "El año no puede ser antes de 1910.",
            ],
            'gender' => [
                'required' => "Se requiere el género",
            ],
            'user' => [
                'already_exists' => "El usuario ya existe",
                'not_found' => "Usuario no encontrado",
                'already_authenticated' => "El usuario ya se ha autenticado",
            ],
        ],

        'Success' => [
            'login' => "¡Has iniciado sesión!",
            'reset_password' => "¡Tu contraseña se ha restablecido!",
            'verify_email' => "Un nuevo correo de verificación ha sido enviado a tu dirección de correo electrónico.",
            'password_reset_success' => "¡Tu contraseña se ha restablecido!",
            'email_verified' => "¡Tu correo electrónico se ha verificado!",
        ],
    ],
];