# Casos de prueba - Configuracion (Tenant)

Fecha: 2026-02-11

## Alcance
Casos de prueba para configuracion del tenant (panel del entrenador):
- General (datos y metodos de pago)
- Notificaciones (correo de contacto y push)
- Apariencia (logo, favicon, colores)

## Rutas y componentes
- General: /dashboard/configuration/general (Tenant Configuration General)
- Notificaciones: /dashboard/configuration/notifications (Tenant Configuration Notification)
- Apariencia: /dashboard/configuration/appearance (Tenant Configuration Appearance)

## Precondiciones
- Usuario con rol Admin/Asistente/Entrenador autenticado en tenant.

## Casos de prueba

### General
TC-TEN-CFG-01 Guardar datos basicos
- Pasos:
  1. Ingresar nombre, redes y WhatsApp.
  2. Guardar.
- Resultado esperado:
  - Se actualiza el nombre del tenant.
  - Se guardan los campos en configuracion.

TC-TEN-CFG-02 Validacion de nombre requerido
- Pasos:
  1. Dejar nombre vacio.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-CFG-03 Metodos de pago transferencia
- Pasos:
  1. Activar transferencia y completar datos bancarios.
  2. Guardar.
- Resultado esperado:
  - Se guardan los campos de transferencia.

TC-TEN-CFG-04 Metodos de pago Mercado Pago
- Pasos:
  1. Activar Mercado Pago.
  2. Completar access token y public key.
  3. Guardar.
- Resultado esperado:
  - Se guardan credenciales e instrucciones.
  - Se muestra URL de webhook.

TC-TEN-CFG-05 Metodos de pago efectivo
- Pasos:
  1. Activar efectivo.
  2. Completar instrucciones.
  3. Guardar.
- Resultado esperado:
  - Se guardan instrucciones.

TC-TEN-CFG-06 Validaciones de longitud
- Pasos:
  1. Ingresar valores que excedan maximos (por ejemplo instrucciones > 500).
  2. Guardar.
- Resultado esperado:
  - Errores de validacion.

### Notificaciones
TC-TEN-CFG-10 Guardar email de contacto
- Pasos:
  1. Ingresar email valido.
  2. Guardar.
- Resultado esperado:
  - Se guarda el email de contacto.

TC-TEN-CFG-11 Email de contacto invalido
- Pasos:
  1. Ingresar email invalido.
  2. Guardar.
- Resultado esperado:
  - Error de validacion.

TC-TEN-CFG-12 Enviar email de prueba
- Precondicion: email valido.
- Pasos:
  1. Usar "test" de email.
- Resultado esperado:
  - Se dispara job de prueba y se muestra confirmacion.

TC-TEN-CFG-13 Enviar push a todos
- Precondicion: dispositivos activos.
- Pasos:
  1. Seleccionar target "all".
  2. Completar titulo y mensaje.
  3. Enviar.
- Resultado esperado:
  - Se envia push a dispositivos activos.
  - Se muestra feedback de envio.

TC-TEN-CFG-14 Enviar push a dispositivo
- Precondicion: dispositivos activos.
- Pasos:
  1. Seleccionar target "device" y un dispositivo.
  2. Completar titulo y mensaje.
  3. Enviar.
- Resultado esperado:
  - Se envia push solo al dispositivo seleccionado.

TC-TEN-CFG-15 Push sin dispositivos
- Precondicion: sin dispositivos activos.
- Pasos:
  1. Intentar enviar push.
- Resultado esperado:
  - Error device_not_found.

TC-TEN-CFG-16 Validaciones de push
- Pasos:
  1. Dejar titulo o mensaje vacio.
  2. Enviar.
- Resultado esperado:
  - Error de validacion.

### Apariencia
TC-TEN-CFG-20 Subir logo valido
- Pasos:
  1. Subir logo JPG/PNG/WEBP <= 20MB.
  2. Guardar.
- Resultado esperado:
  - Se guarda el logo y se actualiza preview.

TC-TEN-CFG-21 Subir favicon valido
- Pasos:
  1. Subir favicon JPG/PNG/WEBP/ICO <= 512KB.
  2. Guardar.
- Resultado esperado:
  - Se guarda el favicon y se actualiza preview.

TC-TEN-CFG-22 Logo invalido por peso o formato
- Pasos:
  1. Subir archivo no permitido o > 20MB.
- Resultado esperado:
  - Error de validacion.

TC-TEN-CFG-23 Favicon invalido por peso o formato
- Pasos:
  1. Subir archivo no permitido o > 512KB.
- Resultado esperado:
  - Error de validacion.

TC-TEN-CFG-24 Eliminar logo o favicon
- Pasos:
  1. Eliminar logo o favicon.
- Resultado esperado:
  - Se borra media y se limpia preview.

TC-TEN-CFG-25 Guardar colores
- Pasos:
  1. Cambiar color_base, color_dark, color_light.
  2. Guardar.
- Resultado esperado:
  - Se guardan los colores en configuracion.

## Notas
- La URL de webhook Mercado Pago se arma con el dominio principal del tenant.
- Los limites de archivo se validan por frontend y backend.
