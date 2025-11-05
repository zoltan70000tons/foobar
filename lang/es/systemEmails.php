<?php
return [
  /*
    |--------------------------------------------------------------------------
    | System Emails Language Lines ES
    |--------------------------------------------------------------------------
    */
  'alerts' => [
    'sent' => 'Correo enviado exitosamente.',
    'send_failed' => 'No se pudo enviar el correo.',
    'already_verified' => 'El correo electrónico ya está verificado.',
    'verification_link_sent' => 'Se envió el enlace de verificación.',
    'not_found' => 'No se encontró el correo electrónico.',
  ],
  'common' => [
    'greeting' => [
      'hi' => 'Hola',
      'hello' => 'Hola :name,',
      'default_name' => 'Cliente',
    ],
    'salutation' => [
      'thanks' => 'Saludos,',
      'regards' => 'Tu equipo de 70000TONS OF METAL',
    ],
    'cta_help' => 'Si no puedes ver el botón, haz clic en el siguiente enlace:',
  ],
  'account' => [
    'activation' => [
      'title' => 'Cuenta creada',
      'welcome' => '¡Estamos emocionados de darte la bienvenida a bordo!',
      'created' => 'Tu cuenta se ha creado correctamente.',
      'activated' => 'Tu cuenta ahora está activa.',
      'survivor_number_label' => 'Tu número de Survivor es:',
      'cta_intro' => 'Activa tu cuenta haciendo clic en el botón a continuación:',
      'cta_label' => 'Activar cuenta',
    ],
    'verification' => [
      'title' => 'Verifica tu correo electrónico',
      'instruction' => 'Haz clic en el botón a continuación para verificar tu correo electrónico:',
      'fallback' => 'Si no creaste una cuenta, no es necesario que realices ninguna acción.',
      'cta_label' => 'Verificar correo electrónico',
    ],
    'update' => [
      'subject' => 'La información de tu cuenta ha sido actualizada',
      'title' => 'Se actualizó el correo electrónico',
      'greeting' => 'Hola :name,',
      'default_name' => 'Cliente',
      'body' => 'El correo electrónico de tu cuenta se actualizó correctamente.',
      'security_notice' =>
        'Si no realizaste este cambio, contacta de inmediato a nuestro equipo de soporte para asegurar tu cuenta.',
      'signature' => 'Equipo de 70000TONS OF METAL',
    ],
  ],
  'invitation' => [
    'request' => [
      'title' => 'Solicitud para agregar pasajero',
      'headline' => ':from te invita a unirte a su cabina para :event',
      'instructions' =>
        'Tienes 72 horas para ingresar tu información en la reserva. Si no proporcionas los datos dentro de ese plazo, la solicitud se cancelará.',
      'account_notice' => 'Tienes una invitación en tu cuenta.',
      'account_instructions' =>
        'Tienes 72 horas para aceptar esta invitación. Si no la aceptas dentro de este plazo, se cancelará. Puedes revisar tus invitaciones en la página de Reservas de tu cuenta. Si tienes alguna pregunta, contacta a nuestro equipo de Servicio al Cliente.',
      'cta' => [
        'add_details' => 'Agregar detalles',
        'complete_form' => 'Completar el formulario',
        'login' => 'Iniciar sesión en tu cuenta',
      ],
      'booking_code_label' => 'Código de reserva',
    ],
  ],
  'password' => [
    'reset' => [
      'subject' => 'Solicitaste un restablecimiento de contraseña',
      'title' => 'Restablecer contraseña',
      'intro' => 'Recibiste este correo porque solicitaste restablecer la contraseña de tu cuenta.',
      'cta_label' => 'Restablecer contraseña',
    ],
    'confirmation' => [
      'subject' => 'Tu contraseña ha sido restablecida',
      'title' => 'Confirmación de restablecimiento de contraseña',
      'intro' => 'Queremos informarte que tu contraseña se restableció correctamente.',
    ],
  ],
  'seat' => [
    'reset' => [
      'subject' => 'Has sido removido de tu reserva',
      'intro' => 'Queremos informarte que has sido removido de tu reserva.',
      'questions' =>
        'Si tienes alguna pregunta, comunícate con el Pasajero Principal de tu cabina para obtener más información.',
    ],
  ],
];
