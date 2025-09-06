@component('mail::message')
# ¡Bienvenido a {{env('APP_NAME','LNQ-Core')}} !

Tu sitio ya está disponible en:

@component('mail::button', ['url' => 'https://' . $domain])
Ir al sitio
@endcomponent

Tus credenciales:

- Usuario: **{{ $adminEmail }}**
- Contraseña: **{{ $password }}**

Podrás cambiarla desde tu perfil una vez que inicies sesión.

¡Gracias por confiar en nosotros!

@endcomponent
