<?php

return [
    'configuration' => [
        'general' => [
            'title' => 'General',
            'subtitle' => 'Gestiona la configuración general y métodos de pago',
            'site_name' => 'Nombre del sitio',
            'color' => 'Color',
            'whatsapp' => 'Whatsapp',
            'social_title' => 'Redes sociales',

            // Métodos de pago
            'payment_methods_title' => 'Métodos de Pago Aceptados',
            'payment_methods_description' => 'Configurá los métodos de pago que aceptás de tus alumnos.',

            'accepts_transfer' => 'Acepto Transferencia/Depósito bancario',
            'bank_name' => 'Nombre del Banco',
            'bank_account_holder' => 'Titular de la Cuenta (opcional)',
            'bank_cuit_cuil' => 'CUIT/CUIL (opcional)',
            'bank_cbu' => 'CBU',
            'bank_alias' => 'Alias',

            'accepts_mercadopago' => 'Acepto Mercadopago (QR o API)',
            'mp_access_token' => 'Access Token',
            'mp_access_token_help' => 'Token de tu aplicación de Mercadopago.',
            'mp_public_key' => 'Public Key (opcional)',
            'mp_public_key_help' => 'Clave pública para integraciones web.',

            'accepts_cash' => 'Acepto Efectivo',
            'cash_instructions' => 'Instrucciones (opcional)',
            'cash_instructions_placeholder' => 'Ej: 5% de descuento pagando antes del vencimiento',
        ],
        'notification' => [
            'title' => 'Notificaciones',
            'subtitle' => 'Gestiona las notificaciones de tu sitio',
            'contact_email' => 'Correo electrónico de contacto',
            'push' => [
                'title' => 'Push manual',
                'subtitle' => 'Envia notificaciones push a los dispositivos activos del tenant.',
                'target' => 'Destino',
                'target_all' => 'Todos los dispositivos activos',
                'target_device' => 'Un dispositivo especifico',
                'device' => 'Dispositivo',
                'device_placeholder' => 'Selecciona un dispositivo',
                'device_required' => 'Debes seleccionar un dispositivo.',
                'device_not_found' => 'No se encontraron dispositivos activos para el destino elegido.',
                'active_devices' => 'Dispositivos activos: :count',
                'no_devices' => 'Todavia no hay dispositivos activos registrados para este tenant.',
                'title_label' => 'Titulo',
                'message' => 'Mensaje',
                'send' => 'Enviar push',
                'disabled' => 'El envio push esta deshabilitado. Activa EXPO_PUSH_ENABLED para usar esta funcion.',
                'tenant_not_available' => 'No se pudo resolver el tenant actual para enviar la notificacion.',
                'sent_success' => 'Push enviado a :sent dispositivos.',
                'sent_partial' => 'Push enviado a :sent dispositivos. Fallos: :errors.',
                'send_failed' => 'No se pudo entregar el push al destino seleccionado.',
            ],
        ],
        'appearance' => [
            'title' => 'Apariencia',
            'subtitle' => 'Gestiona la apariencia de tu sitio',
            'logo' => 'Logo',
            'favicon' => 'Favicon',
        ],

        'landing' => [

            // Campos
            'landing_whatsapp' => 'WhatsApp',
            'landing_instagram' => 'Instagram',
            'landing_facebook' => 'Facebook',
            'landing_youtube' => 'YouTube',
            'landing_twitter' => 'Twitter / X',
            'landing_tiktok' => 'TikTok',

            // Helpers
            'helper_whatsapp' => 'Ingresá solo el número, sin + ni espacios.',
            'helper_instagram' => 'Ingresá solo tu usuario, sin @.',
            'helper_facebook' => 'Ingresá solo el nombre de tu página o perfil.',
            'helper_youtube' => 'Ingresá el nombre de tu canal.',
            'helper_twitter' => 'Ingresá solo tu usuario, sin @.',
            'helper_tiktok' => 'Ingresá solo tu usuario.',
        ],

    ],

    'landing' => [

        'general' => [
            'title' => 'General',
            'subtitle' => 'Gestiona la configuración de la landing de tu sitio',
            'cover' => 'Portada (imagen de fondo)',
            'landing_title' => 'Título de la landing',
            'landing_subtitle' => 'Subtítulo de la landing',
            'landing_whatsapp' => 'Whatsapp',
            'landing_description' => 'Descripción de la landing',
            'landing_footer' => 'Footer de la landing',
            'landing_footer_text_color' => 'Color del texto del footer',
            'landing_footer_background_color' => 'Color de fondo del footer',

        ],

        'cards' => [
            'title' => 'Tarjetas de Home',
            'subtitle' => 'Configura las terjetas de tu sitio ',
            'title_field' => 'Título',
            'title_section' => 'Título de sección',
            'subtitle_section' => 'Subtítulo de sección',

            'text' => 'Texto',
            'link' => 'Enlace',
            'target' => 'Destino',
            'order' => 'Orden',
            'image' => 'Imagen (opcional)',
            '_self' => 'Misma pestaña',
            '_blank' => 'Nueva pestaña',
        ],

        'banners' => [
            'title' => 'Banners',
            'subtitle' => 'Configura los banners de tu sitio',
            'text' => 'Texto',
            'link' => 'Enlace',
            'target' => 'Destino',
            'order' => 'Orden',
            'image' => 'Imagen',
            'desktop_image' => 'Imagen de escritorio',
            'mobile_image' => 'Imagen de movil',

            '_self' => 'Misma pestaña',
            '_blank' => 'Nueva pestaña',
            'title_section' => 'Título de sección',
            'subtitle_section' => 'Subtítulo de sección',
        ],

        'booklets' => [
            'title' => 'Confían en nosotros',
            'subtitle' => 'Muestra las marcas, clientes o aliados de tu sitio',
            'text' => 'Texto',
            'link' => 'Enlace',
            'landing_booklet_title' => 'Título del banner de confianza',
            'target' => 'Destino',
            'order' => 'Orden',
            'image' => 'Imagen / Icono ',
            '_self' => 'Misma pestaña',
            '_blank' => 'Nueva pestaña',
            'title_section' => 'Título de sección',
            'subtitle_section' => 'Subtítulo de sección',
            'active' => 'Activo',
            'show' => 'Mostrar sección',

        ],

    ],

    'status' => [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'suspended' => 'Suspendido',
        'deleted' => 'Eliminado',
    ],
    'site' => [
        'cover' => 'Portada',
        'title' => 'Título',
        'subtitle' => 'Subtítulo',
        'description' => 'Descripción',
        'footer' => 'Footer',
        'whatsapp' => 'WhatsApp',
    ],

];
