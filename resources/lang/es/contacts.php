<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Textos del módulo Contactos
    |--------------------------------------------------------------------------
    */

    // Header del listado
    'index_title'       => 'Contactos recibidos',
    'index_subheading'  => 'Consultas y mensajes enviados desde la web.',

    // Placeholder y etiquetas
    'search_placeholder' => 'Buscar por nombre, email o teléfono',

    // Columnas
    'name'    => 'Nombre',
    'email'   => 'Email',
    'mobile'  => 'Teléfono',
    'message' => 'Mensaje',
    'status'  => 'Estado',
    'actions' => 'Acciones',

    // Estados (por si más adelante hay workflow o categorización)
    'status' => [
        'new'      => 'Nuevo',
        'replied'  => 'Respondido',
        'archived' => 'Archivado',
    ],

    // Acciones
    'view'   => 'Ver',
    'edit'   => 'Editar',
    'delete' => 'Eliminar',

    // Textos comunes de confirmación (usados en modales)
    'delete_title'       => 'Eliminar contacto',
    'delete_msg'         => '¿Estás seguro de que querés eliminar este contacto? Esta acción no se puede deshacer.',
    'confirm_delete'     => 'Sí, eliminar',
    'cancel'             => 'Cancelar',

    // Estados vacíos
    'empty_state' => 'No se encontraron contactos.',

];
