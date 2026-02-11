# Casos de prueba - Alta de Tenant (Central)

Fecha: 2026-02-11

## Alcance
Casos de prueba del alta de Tenant desde el panel central (Clientes):
- Creacion de tenant y subdominio
- Validaciones de formulario
- Creacion de usuario administrador en el tenant
- Asociacion de plan comercial y estado
- Reglas de slug y dominios reservados

## Ruta y componente
- Crear tenant: /dashboard/clients/create (ClientsForm)

## Roles y permisos
- Acceso: solo usuarios con acceso al panel central.

## Datos de prueba sugeridos
- Nombre: "Entrenador Demo"
- Slug valido: "entrenador-demo"
- Email admin: "admin+demo@fittrack.test"
- Password admin: "ClaveSegura123"
- Estado: active
- Plan comercial: uno existente

## Casos de prueba

### Validaciones de formulario
TC-TEN-01 Nombre requerido
- Pasos:
  1. Dejar nombre vacio.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-02 Nombre maximo 24
- Pasos:
  1. Ingresar nombre > 24 caracteres.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-03 Nombre reservado
- Pasos:
  1. Ingresar nombre que genere slug reservado (ej: "admin").
  2. Guardar.
- Resultado esperado:
  - Muestra error: nombre no disponible.

TC-TEN-04 Slug requerido
- Pasos:
  1. Dejar slug vacio.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-05 Slug formato invalido
- Pasos:
  1. Ingresar slug con mayusculas o caracteres invalidos.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-06 Slug longitud fuera de rango
- Pasos:
  1. Ingresar slug < 3 o > 32.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-07 Slug duplicado
- Precondicion: existe tenant con id = "entrenador-demo".
- Pasos:
  1. Crear tenant con mismo slug.
  2. Guardar.
- Resultado esperado:
  - Muestra error por slug duplicado.

TC-TEN-08 Email admin requerido
- Pasos:
  1. Dejar email admin vacio.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-09 Email admin formato invalido
- Pasos:
  1. Ingresar email sin formato valido.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-10 Password admin requerido
- Pasos:
  1. Dejar password vacio.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-11 Password admin minimo 8
- Pasos:
  1. Ingresar password con menos de 8 caracteres.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-12 Password igual al nombre
- Pasos:
  1. Ingresar password igual al nombre del entrenador (ignorando mayusculas).
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

TC-TEN-13 Estado requerido
- Pasos:
  1. Dejar estado sin seleccionar.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

### Creacion de tenant
TC-TEN-20 Alta exitosa con datos validos
- Pasos:
  1. Completar todos los campos con datos validos.
  2. Guardar.
- Resultado esperado:
  - Se crea un tenant con id = slug.
  - Se asigna status y plan comercial (si corresponde).
  - Se crea dominio principal {slug}.APP_DOMAIN.
  - Se crea usuario administrador en el tenant con rol Admin.
  - Se muestra mensaje de exito y redirecciona al listado.

TC-TEN-21 Preview de dominio
- Pasos:
  1. Ingresar nombre y/o slug.
- Resultado esperado:
  - Se muestra vista previa del dominio completo.

TC-TEN-22 Slug autogenerado desde nombre
- Pasos:
  1. Ingresar nombre y no editar manualmente el slug.
- Resultado esperado:
  - El slug se sugiere y se sincroniza con el nombre.

TC-TEN-23 Slug editado manualmente
- Pasos:
  1. Editar el slug manualmente.
  2. Cambiar el nombre.
- Resultado esperado:
  - El slug no se sobrescribe al cambiar el nombre.

### Reglas de dominio reservado
TC-TEN-30 Slug reservado en lista negra
- Precondicion: slug en lista reservada (www, admin, mail, api, ftp, cpanel, webmail, lunico, test).
- Pasos:
  1. Usar slug reservado.
  2. Guardar.
- Resultado esperado:
  - Muestra error de validacion.

## Notas
- El alta crea automaticamente el dominio principal y el usuario Admin dentro del tenant.
- Verificar que el dominio principal sea no editable en gestion de dominios (solo en modo edicion).
- El plan comercial es opcional si no se selecciona.
