# Casos de prueba - Chat (Tenant)

Fecha: 2026-02-11

## Alcance
Casos de prueba para el uso del chat del entrenador:
- Conversaciones con alumnos
- Conversacion con soporte central
- Envio y lectura de mensajes

## Rutas y componentes
- Chats con alumnos: /dashboard/messages/conversations (Tenant Messages Index)
- Conversacion con alumno: /dashboard/messages/conversations/{conversation} (Tenant Messages Show)
- Soporte central: /dashboard/support (Tenant Support Show)

## Precondiciones
- Usuario con rol Admin/Asistente/Entrenador autenticado en tenant.
- Alumnos existentes para iniciar conversacion.

## Casos de prueba

### Chat con alumnos
TC-TEN-CHAT-01 Ver listado de conversaciones
- Pasos:
  1. Ingresar a /dashboard/messages/conversations.
- Resultado esperado:
  - Se muestra listado paginado de conversaciones.
  - Se ve ultimo mensaje y contador de no leidos.

TC-TEN-CHAT-02 Estado vacio
- Precondicion: sin conversaciones.
- Pasos:
  1. Ingresar al listado.
- Resultado esperado:
  - Se muestra estado vacio.

TC-TEN-CHAT-03 Iniciar conversacion
- Precondicion: alumno sin conversacion activa.
- Pasos:
  1. Abrir modal Nueva conversacion.
  2. Seleccionar alumno.
  3. Confirmar.
- Resultado esperado:
  - Se crea o reutiliza la conversacion.
  - Redireccion a la vista de conversacion.

TC-TEN-CHAT-04 Validacion de alumno requerido
- Pasos:
  1. Intentar iniciar conversacion sin alumno.
- Resultado esperado:
  - Error de validacion.

TC-TEN-CHAT-05 Enviar mensaje valido
- Pasos:
  1. Escribir mensaje <= 5000 caracteres.
  2. Enviar.
- Resultado esperado:
  - Mensaje enviado y campo limpiado.

TC-TEN-CHAT-06 Enviar mensaje vacio
- Pasos:
  1. Enviar sin contenido.
- Resultado esperado:
  - Error de validacion.

TC-TEN-CHAT-07 Marcar mensajes como leidos
- Precondicion: existen mensajes no leidos.
- Pasos:
  1. Abrir conversacion.
- Resultado esperado:
  - Los mensajes se marcan como leidos.
  - Se muestra separador de no leidos en el primer mensaje no leido.

TC-TEN-CHAT-08 Acceso a conversacion ajena
- Precondicion: conversacion de otro tenant.
- Pasos:
  1. Intentar abrir la conversacion.
- Resultado esperado:
  - Acceso denegado por Gate.

### Chat con soporte central
TC-TEN-SUP-01 Ver chat de soporte
- Pasos:
  1. Ingresar a /dashboard/support.
- Resultado esperado:
  - Se crea o reutiliza conversacion con soporte.
  - Se muestra historial de mensajes.

TC-TEN-SUP-02 Enviar mensaje a soporte
- Pasos:
  1. Escribir mensaje <= 5000 caracteres.
  2. Enviar.
- Resultado esperado:
  - Mensaje enviado y campo limpiado.

TC-TEN-SUP-03 Enviar mensaje vacio
- Pasos:
  1. Enviar sin contenido.
- Resultado esperado:
  - Error de validacion.

TC-TEN-SUP-04 Marcar mensajes como leidos en soporte
- Precondicion: mensajes no leidos.
- Pasos:
  1. Foco en la ventana o enviar mensaje.
- Resultado esperado:
  - Se marcan como leidos.
  - Se mantiene separador de no leidos si aplica.

## Notas
- El listado de conversaciones muestra contador de no leidos por alumno.
- El chat usa poll para actualizar mensajes y dispara notificacion de titulo si llegan nuevos.
