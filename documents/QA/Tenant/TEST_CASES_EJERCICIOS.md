# Casos de prueba - Ejercicios (Tenant)

Fecha: 2026-02-11

## Alcance
Casos de prueba para alta y modificacion de ejercicios en el panel del entrenador:
- Alta de ejercicio con datos basicos
- Modificacion de ejercicio existente
- Validaciones de formulario
- Carga y gestion de imagenes

## Rutas y componentes
- Crear ejercicio: /dashboard/exercises/create (Tenant Exercises Form)
- Editar ejercicio: /dashboard/exercises/{exercise}/edit (Tenant Exercises Form)

## Precondiciones
- Usuario con rol Admin/Asistente/Entrenador autenticado en tenant.

## Datos de prueba sugeridos
- Nombre: "Sentadilla"
- Categoria: "Piernas"
- Nivel: "beginner"
- Equipo: "Barra"
- Activo: true
- Imagenes: 1-3 JPG/PNG/WebP <= 2MB

## Casos de prueba

### Alta de ejercicio
TC-TEN-EJ-01 Alta exitosa con datos minimos
- Pasos:
  1. Completar nombre.
  2. Guardar.
- Resultado esperado:
  - Se crea el ejercicio.
  - Redirecciona a edicion si no se marco "volver".

TC-TEN-EJ-02 Alta con campos opcionales
- Pasos:
  1. Completar nombre, descripcion, categoria, nivel, equipo.
  2. Guardar.
- Resultado esperado:
  - Se guardan todos los campos.

TC-TEN-EJ-03 Alta con estado inactivo
- Pasos:
  1. Desmarcar "activo".
  2. Guardar.
- Resultado esperado:
  - El ejercicio queda inactivo.

TC-TEN-EJ-04 Volver al listado
- Pasos:
  1. Marcar "volver al listado".
  2. Guardar.
- Resultado esperado:
  - Redireccion a /dashboard/exercises.

### Modificacion de ejercicio
TC-TEN-EJ-10 Editar campos basicos
- Precondicion: ejercicio existente.
- Pasos:
  1. Modificar nombre, descripcion, categoria, nivel o equipo.
  2. Guardar.
- Resultado esperado:
  - Cambios persistidos.

TC-TEN-EJ-11 Cambiar estado activo/inactivo
- Pasos:
  1. Alternar el checkbox de activo.
  2. Guardar.
- Resultado esperado:
  - El estado se actualiza.

### Validaciones
TC-TEN-EJ-20 Nombre requerido
- Pasos:
  1. Dejar nombre vacio.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-EJ-21 Nombre maximo 255
- Pasos:
  1. Ingresar nombre > 255 caracteres.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-EJ-22 Imagen invalida
- Pasos:
  1. Subir archivo no imagen.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-EJ-23 Imagen con peso > 2MB
- Pasos:
  1. Subir imagen mayor a 2MB.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

### Imagenes
TC-TEN-EJ-30 Agregar imagenes validas
- Pasos:
  1. Subir 1 o mas imagenes validas.
  2. Guardar.
- Resultado esperado:
  - Las imagenes se guardan en la coleccion images.

TC-TEN-EJ-31 Remover imagen pendiente
- Pasos:
  1. Subir imagen.
  2. Quitarla desde la grilla de pendientes.
- Resultado esperado:
  - La imagen no se guarda.

TC-TEN-EJ-32 Eliminar imagen existente
- Precondicion: ejercicio con imagenes guardadas.
- Pasos:
  1. Eliminar una imagen.
- Resultado esperado:
  - La imagen se elimina de la coleccion.

TC-TEN-EJ-33 Limite maximo de imagenes
- Precondicion: maximo permitido 16.
- Pasos:
  1. Subir imagenes hasta el maximo.
  2. Intentar agregar mas.
- Resultado esperado:
  - No permite superar el maximo (se recorta a 16).

## Notas
- El formulario usa imagenes pendientes y las guarda al hacer submit.
- El maximo de imagenes permitido es 16 por ejercicio.
