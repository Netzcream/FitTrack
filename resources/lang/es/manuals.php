<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Manuales y Guías - Central
    |--------------------------------------------------------------------------
    */

    // Títulos
    'index_title'       => 'Manuales y guías',
    'index_subheading'  => 'Administra, crea o edita guías y recursos para los Personal Trainers.',
    'new_manual'        => 'Nuevo manual/guía',
    'edit_manual'       => 'Editar manual/guía',
    'create_title'      => 'Crear nuevo manual/guía',
    'edit_title'        => 'Editar manual/guía',

    // Formulario
    'title'             => 'Título',
    'title_placeholder' => 'Ej: Configuración de perfil',
    'category'          => 'Categoría',
    'category_select'   => 'Seleccioná una',
    'summary'           => 'Resumen',
    'summary_placeholder' => 'Breve descripción (opcional)',
    'content'           => 'Contenido',
    'content_placeholder' => 'Ingresá el contenido del manual (HTML o texto enriquecido permitido)',
    'icon'              => 'Ícono/Imagen',
    'attachments'       => 'Adjuntar archivos (PDF, video, etc.)',
    'is_active'         => 'Activo',
    'published_at'      => 'Fecha de publicación',
    'sort_order'        => 'Orden',

    // Filtros y búsqueda
    'search_placeholder' => 'Buscar por título, palabra clave, etc.',
    'filter_category'    => 'Categoría',

    // Categorías
    'categories' => [
        'configuration' => 'Configuración',
        'training'      => 'Entrenamiento',
        'nutrition'     => 'Nutrición',
        'support'       => 'Soporte',
        'general'       => 'General',
    ],

    // Estados
    'active'            => 'Activo',
    'inactive'          => 'Inactivo',
    'published'         => 'Publicado',
    'draft'             => 'Borrador',

    // Listado
    'showing'           => 'Mostrando :from-:to de :total manuales',
    'updated_at'        => 'Actualizado',
    'reading_time'      => ':minutes min de lectura',

    // Mensajes
    'created_success'   => 'Manual creado correctamente.',
    'updated_success'   => 'Manual actualizado correctamente.',
    'deleted_success'   => 'Manual eliminado correctamente.',
    'published_success' => 'Manual publicado correctamente.',
    'error_delete'      => 'Error al eliminar el manual: :error',

    // Modal de eliminación
    'delete_confirm_title' => '¿Eliminar manual?',
    'delete_confirm_msg'   => 'Esta acción eliminará el manual seleccionado. Los archivos adjuntos también serán eliminados. ¿Estás seguro?',

    // Empty state
    'no_manuals'        => 'No hay manuales disponibles.',
    'no_results'        => 'No se encontraron manuales que coincidan con tu búsqueda.',
];
