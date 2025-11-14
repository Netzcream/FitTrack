<?php

return [
    'configuration' => [
        'general' => [
            'title' => 'General',
            'subtitle' => 'Gestiona la configuración de tu sitio',
            'site_name' => 'Nombre del sitio',
            'color' => 'Color',
            'whatsapp' => 'Whatsapp',
            'social_title' => 'Redes sociales',
        ],
        'notification' => [
            'title' => 'Notificaciones',
            'subtitle' => 'Gestiona las notificaciones de tu sitio',
            'contact_email' => 'Correo electrónico de contacto',
        ],
        'appearance' => [
            'title' => 'Apariencia',
            'subtitle' => 'Gestiona la apariencia de tu sitio',
            'logo' => 'Logo',
            'favicon' => 'Favicon',
        ],

        'landing' => [

            // Campos
            'landing_whatsapp'   => 'WhatsApp',
            'landing_instagram'  => 'Instagram',
            'landing_facebook'   => 'Facebook',
            'landing_youtube'    => 'YouTube',
            'landing_twitter'    => 'Twitter / X',
            'landing_tiktok'     => 'TikTok',

            // Helpers
            'helper_whatsapp'  => 'Ingresá solo el número, sin + ni espacios.',
            'helper_instagram' => 'Ingresá solo tu usuario, sin @.',
            'helper_facebook'  => 'Ingresá solo el nombre de tu página o perfil.',
            'helper_youtube'   => 'Ingresá el nombre de tu canal.',
            'helper_twitter'   => 'Ingresá solo tu usuario, sin @.',
            'helper_tiktok'    => 'Ingresá solo tu usuario.',
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
            'show'   => 'Mostrar sección',

        ],




    ],

    'status' => [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'suspended' => 'Suspendido',
        'deleted' => 'Eliminado',
    ],


];
