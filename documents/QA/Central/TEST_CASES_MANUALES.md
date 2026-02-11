# Casos de prueba - Manuales

Fecha: 2026-02-11

## Alcance
Este documento cubre los casos de prueba del modulo de Manuales (central y tenant):
- Administracion en central (listar, crear, editar, eliminar, adjuntos, icono)
- Consulta en tenant (listado, filtros, detalle, descargas)
- Reglas de visibilidad (publicado, activo, fecha futura)
- Validaciones de formulario

## Rutas y componentes
- Central:
  - Listado: /dashboard/manuals (ManualsIndex)
  - Crear: /dashboard/manuals/create (ManualsForm)
  - Editar: /dashboard/manuals/{manual}/edit (ManualsForm)
- Tenant:
  - Listado: /dashboard/manuals (ManualsIndex)
  - Detalle: /dashboard/manuals/{manual:uuid} (ManualsShow)

## Roles y permisos
- Solo super_admin puede crear, editar o eliminar manuales.
- Cualquier usuario autenticado puede ver manuales publicados y activos.

## Datos de prueba sugeridos
- Manual A: is_active=true, published_at=hoy-1, category=training, summary y content completos.
- Manual B: is_active=true, published_at=hoy+7 (futuro).
- Manual C: is_active=false, published_at=hoy-1.
- Manual D: is_active=true, published_at=null.
- Manual E: is_active=true, published_at=hoy-1, con icono y 2 adjuntos.

## Casos de prueba

### Central - Listado
TC-CEN-01 Listado inicial sin filtros
- Precondicion: existen manuales en la base.
- Pasos:
  1. Ingresar al listado /dashboard/manuals.
- Resultado esperado:
  - Se muestra la tabla con manuales.
  - Se visualizan titulo, categoria, estado, actualizado y acciones.

TC-CEN-02 Filtro por categoria
- Precondicion: manuales con varias categorias.
- Pasos:
  1. Seleccionar una categoria en el filtro.
- Resultado esperado:
  - Solo se muestran manuales de esa categoria.

TC-CEN-03 Busqueda por titulo/resumen
- Pasos:
  1. Buscar un termino que exista en titulo o resumen.
- Resultado esperado:
  - Se muestran coincidencias por titulo o resumen.

TC-CEN-04 Ordenar por titulo
- Pasos:
  1. Click en la cabecera de titulo.
  2. Click nuevamente para invertir orden.
- Resultado esperado:
  - Cambia a ascendente/descendente.

TC-CEN-05 Ordenar por actualizado
- Pasos:
  1. Click en la cabecera de actualizado.
- Resultado esperado:
  - Se ordena por updated_at.

TC-CEN-06 Eliminar manual (confirmacion)
- Precondicion: usuario super_admin.
- Pasos:
  1. Click en eliminar.
  2. Confirmar en modal.
- Resultado esperado:
  - Manual eliminado (soft delete).
  - Se muestra notificacion de exito.

TC-CEN-07 Eliminar manual (cancelar)
- Pasos:
  1. Click en eliminar.
  2. Cancelar en modal.
- Resultado esperado:
  - No se elimina el manual.

### Central - Crear manual
TC-CEN-10 Crear manual con datos minimos
- Pasos:
  1. Completar titulo, categoria y contenido.
  2. Guardar.
- Resultado esperado:
  - Se crea el manual.
  - Si slug vacio, se genera automaticamente.
  - Redirecciona a editar si no se marco volver.

TC-CEN-11 Validacion de titulo requerido
- Pasos:
  1. Dejar titulo vacio y guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-CEN-12 Validacion de categoria requerida
- Pasos:
  1. Dejar categoria vacia y guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-CEN-13 Validacion de contenido requerido
- Pasos:
  1. Dejar contenido vacio y guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-CEN-14 Slug unico
- Precondicion: existe un manual con slug X.
- Pasos:
  1. Crear manual con slug X.
- Resultado esperado:
  - Muestra error por slug duplicado.

TC-CEN-15 Resumen maximo 500
- Pasos:
  1. Ingresar resumen > 500 caracteres y guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-CEN-16 Fecha de publicacion valida
- Pasos:
  1. Ingresar fecha invalida.
- Resultado esperado:
  - Muestra error de validacion.

TC-CEN-17 Checkbox activo
- Pasos:
  1. Desactivar el manual y guardar.
- Resultado esperado:
  - El manual queda inactivo.

TC-CEN-18 Volver al listado
- Pasos:
  1. Marcar checkbox Volver a la lista.
  2. Guardar.
- Resultado esperado:
  - Redirecciona al listado.

### Central - Editar manual
TC-CEN-20 Editar campos basicos
- Precondicion: existe manual.
- Pasos:
  1. Editar titulo, resumen, contenido.
  2. Guardar.
- Resultado esperado:
  - Cambios persistidos.

TC-CEN-21 Slug editable
- Pasos:
  1. Cambiar slug a uno valido.
  2. Guardar.
- Resultado esperado:
  - Slug actualizado.

### Central - Icono
TC-CEN-30 Subir icono valido
- Pasos:
  1. Seleccionar imagen JPG/PNG/WebP/SVG <= 2MB.
  2. Guardar.
- Resultado esperado:
  - Se guarda el icono en la coleccion icon.

TC-CEN-31 Icono invalido por peso
- Pasos:
  1. Seleccionar imagen > 2MB.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-CEN-32 Eliminar icono
- Precondicion: manual con icono.
- Pasos:
  1. Click en Eliminar icono.
- Resultado esperado:
  - Se elimina el icono de la coleccion.

TC-CEN-33 Quitar preview
- Pasos:
  1. Seleccionar icono.
  2. Click en Quitar.
- Resultado esperado:
  - Se limpia el preview sin guardar.

### Central - Adjuntos
TC-CEN-40 Adjuntos validos
- Pasos:
  1. Subir archivos permitidos (PDF, DOC/DOCX, XLS/XLSX, TXT, JPG/PNG/WebP) <= 10MB.
  2. Guardar.
- Resultado esperado:
  - Se agregan a la coleccion attachments.

TC-CEN-41 Adjuntos invalidos por peso
- Pasos:
  1. Subir archivo > 10MB.
- Resultado esperado:
  - Muestra error de validacion.

TC-CEN-42 Eliminar adjunto existente
- Precondicion: manual con adjuntos.
- Pasos:
  1. Click en Eliminar sobre un adjunto.
  2. Guardar.
- Resultado esperado:
  - El adjunto se elimina.

TC-CEN-43 Eliminar adjunto pendiente
- Pasos:
  1. Subir adjunto.
  2. Quitar de pendientes.
  3. Guardar.
- Resultado esperado:
  - El adjunto no se guarda.

TC-CEN-44 Error parcial de adjuntos
- Pasos:
  1. Subir multiples adjuntos incluyendo uno invalido o inaccesible.
  2. Guardar.
- Resultado esperado:
  - Se guarda el manual.
  - Se muestra warning con archivos fallidos.

### Tenant - Listado
TC-TEN-01 Ver listado de manuales publicados
- Precondicion: hay manuales activos y publicados.
- Pasos:
  1. Ingresar al listado /dashboard/manuals.
- Resultado esperado:
  - Solo aparecen manuales con is_active=true y published_at <= hoy.

TC-TEN-02 No mostrar manuales inactivos
- Precondicion: existe manual inactivo.
- Resultado esperado:
  - No aparece en el listado.

TC-TEN-03 No mostrar manuales sin publicacion
- Precondicion: existe manual con published_at null.
- Resultado esperado:
  - No aparece en el listado.

TC-TEN-04 No mostrar manuales con fecha futura
- Precondicion: existe manual con published_at futuro.
- Resultado esperado:
  - No aparece en el listado.

TC-TEN-05 Buscar por titulo/resumen
- Pasos:
  1. Buscar por texto.
- Resultado esperado:
  - Filtra por titulo o resumen.

TC-TEN-06 Filtrar por categoria
- Pasos:
  1. Seleccionar categoria.
- Resultado esperado:
  - Solo manuales de esa categoria.

### Tenant - Detalle
TC-TEN-10 Ver detalle de manual publicado
- Precondicion: manual publicado.
- Pasos:
  1. Abrir detalle del manual.
- Resultado esperado:
  - Se muestra titulo, resumen, contenido, adjuntos e icono si existen.

TC-TEN-11 Bloqueo de manual no publicado
- Precondicion: manual inactivo o con fecha futura.
- Pasos:
  1. Intentar acceder al detalle por URL.
- Resultado esperado:
  - Respuesta 404.

TC-TEN-12 Descargar adjuntos
- Precondicion: manual con adjuntos.
- Pasos:
  1. Click en Descargar.
- Resultado esperado:
  - Se descarga el archivo correcto.

## Validaciones adicionales
- Slug generado automaticamente si se deja vacio.
- El listado central muestra estado (publicado/activo/inactivo).
- El listado tenant pagina resultados (12 por pagina).
- El listado central pagina resultados (15 por pagina).

## Notas
- Las pruebas de permisos deben verificarse con usuarios sin rol super_admin.
- Verificar que las fechas se guarden en formato correcto y se rendericen dd/mm/yyyy en vistas.
