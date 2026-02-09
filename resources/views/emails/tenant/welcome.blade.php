@component('mail::message')
# ¡Bienvenido a FitTrack!

Tu sitio ya está disponible en:

@component('mail::button', ['url' => 'https://' . $domain])
Ir al sitio
@endcomponent

Tus credenciales:

- Usuario: **{{ $adminEmail }}**
@if (!empty($password))
- Contraseña: **{{ $password }}**
@endif

Podrás cambiarla desde tu perfil una vez que inicies sesión.

¡Gracias por confiar en nosotros!

@endcomponent
