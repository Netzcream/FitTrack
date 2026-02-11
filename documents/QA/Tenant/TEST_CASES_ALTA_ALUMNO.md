# Casos de prueba - Alta de Alumno (Tenant)

Fecha: 2026-02-11

## Alcance
Casos de prueba para el alta de Alumno desde el panel del entrenador (Tenant):
- Creacion de alumno y usuario asociado
- Validaciones de formulario
- Avatar y datos personales
- Envio de invitacion/reset de password

## Ruta y componente
- Crear alumno: /dashboard/students/create (Tenant Students Form)

## Precondiciones
- Usuario con rol Admin/Asistente/Entrenador autenticado en tenant.
- Planes comerciales cargados (opcional).

## Datos de prueba sugeridos
- Nombre: "Juan"
- Apellido: "Perez"
- Email: "juan.perez+fittrack@test.com"
- Estado: active
- Plan comercial: opcional
- Facturacion: monthly
- Estado de cuenta: on_time

## Casos de prueba

### Validaciones basicas
TC-TEN-ALU-01 Nombre requerido
- Pasos:
  1. Dejar nombre vacio.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-ALU-02 Apellido requerido
- Pasos:
  1. Dejar apellido vacio.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-ALU-03 Email requerido y formato
- Pasos:
  1. Dejar email vacio.
  2. Guardar.
  3. Ingresar email invalido.
  4. Guardar.
- Resultado esperado:
  - Error de validacion en ambos casos.

TC-TEN-ALU-04 Email unico
- Precondicion: existe alumno con el mismo email.
- Pasos:
  1. Crear alumno con email duplicado.
- Resultado esperado:
  - Error de validacion por email duplicado.

TC-TEN-ALU-05 Estado requerido
- Pasos:
  1. Dejar estado sin seleccionar.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-ALU-06 Frecuencia de facturacion requerida
- Pasos:
  1. Dejar frecuencia vacia.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-ALU-07 Estado de cuenta requerido
- Pasos:
  1. Dejar estado de cuenta vacio.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

### Alta de alumno
TC-TEN-ALU-10 Alta exitosa con datos validos
- Pasos:
  1. Completar campos obligatorios.
  2. Guardar.
- Resultado esperado:
  - Se crea Student y User asociado.
  - El User recibe rol Alumno.
  - Se dispara evento StudentCreated con link de registro.
  - Redireccion a edicion si no se marco "volver".

TC-TEN-ALU-11 Alta con "volver al listado"
- Pasos:
  1. Marcar "volver a la lista".
  2. Guardar.
- Resultado esperado:
  - Redireccion al listado de alumnos.

### Avatar
TC-TEN-ALU-20 Subir avatar valido
- Pasos:
  1. Subir imagen JPG/PNG/WebP <= 2MB.
  2. Guardar.
- Resultado esperado:
  - Avatar guardado en media collection avatar.

TC-TEN-ALU-21 Avatar invalido por peso
- Pasos:
  1. Subir imagen > 2MB.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-ALU-22 Eliminar avatar
- Precondicion: alumno con avatar.
- Pasos:
  1. Click en eliminar avatar.
- Resultado esperado:
  - Avatar eliminado.

### Datos personales opcionales
TC-TEN-ALU-30 Datos personales opcionales
- Pasos:
  1. Completar fecha de nacimiento, genero, altura, lesiones, contacto de emergencia.
  2. Guardar.
- Resultado esperado:
  - Datos guardados en data del alumno.

TC-TEN-ALU-31 Validaciones de altura y peso
- Pasos:
  1. Ingresar altura o peso con valores invalidos.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

## Notas
- El email del alumno se usa para vincular o crear el User.
- Si el User ya existe, se actualiza el nombre y se asigna el rol Alumno.
- La invitacion se realiza via token de reset de password.
