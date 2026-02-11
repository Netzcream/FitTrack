# Casos de prueba - Entrenamiento (Tenant)

Fecha: 2026-02-11

## Alcance
Casos de prueba para alta y modificacion de entrenamientos (Training Plans) en el panel del entrenador:
- Alta de plan general
- Alta de plan asignado a alumno
- Modificacion de plan existente
- Validaciones de formulario
- Seleccion y orden de ejercicios

## Rutas y componentes
- Crear plan: /dashboard/training-plans/create (Tenant TrainingPlan Form)
- Editar plan: /dashboard/training-plans/{trainingPlan}/edit (Tenant TrainingPlan Form)

## Precondiciones
- Usuario con rol Admin/Asistente/Entrenador autenticado en tenant.
- Ejercicios existentes para seleccionar.
- Alumno existente para plan asignado (opcional).

## Datos de prueba sugeridos
- Nombre: "Plan Fuerza Inicial"
- Objetivo: "Ganar fuerza"
- Duracion: "4 semanas"
- Activo: true
- Ejercicios: 3-5 ejercicios con dia 1-7 y orden
- Alumno: uno existente (solo si se asigna)
- Fechas asignacion: desde hoy hasta +30 dias

## Casos de prueba

### Alta de plan general
TC-TEN-TR-01 Alta exitosa con datos minimos
- Pasos:
  1. Completar nombre.
  2. Guardar.
- Resultado esperado:
  - Se crea el plan.
  - Redirecciona a edicion si no se marco "volver".

TC-TEN-TR-02 Alta con campos opcionales
- Pasos:
  1. Completar nombre, descripcion, objetivo y duracion.
  2. Guardar.
- Resultado esperado:
  - Se guardan todos los campos.

TC-TEN-TR-03 Alta con estado inactivo
- Pasos:
  1. Desmarcar "activo".
  2. Guardar.
- Resultado esperado:
  - El plan queda inactivo.

TC-TEN-TR-04 Volver al listado
- Pasos:
  1. Marcar "volver al listado".
  2. Guardar.
- Resultado esperado:
  - Redireccion a /dashboard/training-plans.

### Alta de plan asignado a alumno
TC-TEN-TR-10 Alta con alumno y fechas
- Precondicion: alumno existente.
- Pasos:
  1. Crear plan con student_id (desde vista del alumno o query student).
  2. Ingresar assigned_from y assigned_until validas.
  3. Guardar.
- Resultado esperado:
  - Plan queda asignado al alumno.
  - Se guardan fechas de vigencia.

TC-TEN-TR-11 Fechas invalidas
- Pasos:
  1. Ingresar assigned_until anterior a assigned_from.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-TR-12 Solapamiento de planes activos
- Precondicion: alumno con plan activo vigente.
- Pasos:
  1. Crear otro plan activo para el mismo alumno con fechas solapadas.
  2. Guardar.
- Resultado esperado:
  - Error de solapamiento (training_plans.overlap_error).

### Modificacion de plan
TC-TEN-TR-20 Editar campos basicos
- Precondicion: plan existente.
- Pasos:
  1. Modificar nombre, descripcion, objetivo o duracion.
  2. Guardar.
- Resultado esperado:
  - Cambios persistidos.

TC-TEN-TR-21 Cambiar estado activo/inactivo
- Pasos:
  1. Alternar el checkbox de activo.
  2. Guardar.
- Resultado esperado:
  - El estado se actualiza.

### Ejercicios del plan
TC-TEN-TR-30 Agregar ejercicios
- Precondicion: ejercicios disponibles.
- Pasos:
  1. Buscar y agregar ejercicios.
  2. Guardar.
- Resultado esperado:
  - Se guarda exercises_data con day, order, detail y notes.

TC-TEN-TR-31 Reordenar ejercicios
- Pasos:
  1. Mover ejercicios arriba/abajo.
  2. Guardar.
- Resultado esperado:
  - El orden se persiste en exercises_data.

TC-TEN-TR-32 Eliminar ejercicio del plan
- Pasos:
  1. Quitar un ejercicio.
  2. Guardar.
- Resultado esperado:
  - El ejercicio se elimina del plan.

TC-TEN-TR-33 Validar dia y notas
- Pasos:
  1. Ingresar dia fuera de 1-7 o notas > 255.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

### Validaciones
TC-TEN-TR-40 Nombre requerido
- Pasos:
  1. Dejar nombre vacio.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-TR-41 Descripcion max 2000
- Pasos:
  1. Ingresar descripcion > 2000 caracteres.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-TR-42 Objetivo max 255
- Pasos:
  1. Ingresar objetivo > 255 caracteres.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-TR-43 Notas max 255
- Pasos:
  1. Ingresar notas > 255 en un ejercicio.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

## Notas
- La busqueda de ejercicios requiere al menos 2 caracteres.
- El plan asignado a alumno redirige al listado del alumno al guardar.
