# Casos de prueba - Student

Fecha: 2026-02-11

## Alcance
Casos de prueba para el panel Student (rol Alumno):
- Acceso y permisos
- Dashboard
- Entrenamiento del dia
- Progreso
- Mensajes
- Pagos e historial de facturas
- Detalle de plan

## Rutas y componentes
- /student (Dashboard)
- /student/workout-today (WorkoutToday)
- /student/workout/{workout} (WorkoutToday)
- /student/progress (Progress)
- /student/messages (Messages)
- /student/payments (Payments)
- /student/payments-callback (PaymentController)
- /student/invoices (InvoicesHistory)
- /student/plan/{assignment} (PlanDetail)
- /student/plan/{assignment}/download (StudentPlanController)

## Precondiciones generales
- Usuario autenticado con rol Alumno.
- Acceso student habilitado (EnsureStudentAccessEnabled).

## Casos de prueba

### Acceso y permisos
TC-STU-01 Acceso con rol Alumno
- Pasos:
  1. Ingresar a /student.
- Resultado esperado:
  - Acceso permitido al dashboard.

TC-STU-02 Acceso sin rol Alumno
- Pasos:
  1. Ingresar a /student con otro rol.
- Resultado esperado:
  - Acceso denegado o redireccion segun middleware.

TC-STU-03 Acceso student deshabilitado
- Pasos:
  1. Deshabilitar acceso student en el tenant.
  2. Ingresar a /student.
- Resultado esperado:
  - Acceso bloqueado por middleware EnsureStudentAccessEnabled.

### Dashboard
TC-STU-10 Dashboard con plan activo
- Precondicion: alumno con plan activo y workout del dia.
- Pasos:
  1. Ingresar a /student.
- Resultado esperado:
  - Se muestra plan activo, progreso y boton de iniciar/continuar entrenamiento.

TC-STU-11 Dashboard sin plan activo
- Precondicion: alumno sin asignacion activa.
- Pasos:
  1. Ingresar a /student.
- Resultado esperado:
  - Se muestra mensaje "No tenes un plan activo".

TC-STU-12 Iniciar entrenamiento desde dashboard
- Precondicion: workout del dia disponible.
- Pasos:
  1. Click en iniciar/continuar.
- Resultado esperado:
  - Redireccion a /student/workout/{workout}.
  - Mensaje de inicio o continuacion.

TC-STU-13 Indicador de pago pendiente
- Precondicion: invoice pendiente para el alumno.
- Pasos:
  1. Ingresar a /student.
- Resultado esperado:
  - Se indica pago pendiente.

### Entrenamiento del dia
TC-STU-20 Acceder a workout activo
- Precondicion: alumno con workout in_progress.
- Pasos:
  1. Ingresar a /student/workout-today.
- Resultado esperado:
  - Se carga el entrenamiento activo.

TC-STU-21 Acceder a workout ajeno
- Precondicion: existe workout de otro alumno.
- Pasos:
  1. Ingresar a /student/workout/{workout} con id ajeno.
- Resultado esperado:
  - Respuesta 403.

TC-STU-22 Sin workout activo
- Precondicion: alumno sin workout in_progress.
- Pasos:
  1. Ingresar a /student/workout-today.
- Resultado esperado:
  - Mensaje de error y redireccion al dashboard.

TC-STU-23 Marcar ejercicio completado
- Precondicion: workout activo con ejercicios.
- Pasos:
  1. Marcar un ejercicio como completado.
- Resultado esperado:
  - Se persiste estado completado.
  - Se dispara feedback de XP si corresponde.

TC-STU-24 Completar entrenamiento con datos validos
- Pasos:
  1. Completar duracion, rating opcional y notas.
  2. Finalizar entrenamiento.
- Resultado esperado:
  - Workout pasa a completed.
  - Redireccion al dashboard con mensaje de exito.

TC-STU-25 Completar entrenamiento con validaciones
- Pasos:
  1. Ingresar duracion fuera de rango o rating > 5.
  2. Finalizar entrenamiento.
- Resultado esperado:
  - Se muestran errores de validacion.

TC-STU-26 Registrar peso al completar
- Pasos:
  1. Completar entrenamiento ingresando peso valido.
- Resultado esperado:
  - Se guarda entrada de peso en StudentWeightEntry.

TC-STU-27 Saltar entrenamiento
- Pasos:
  1. Usar opcion de saltar entrenamiento.
- Resultado esperado:
  - Workout marcado como skipped.
  - Redireccion al dashboard.

### Progreso
TC-STU-30 Ver progreso
- Pasos:
  1. Ingresar a /student/progress.
- Resultado esperado:
  - Se muestran estadisticas y graficos de progreso.

TC-STU-31 Historial de peso
- Precondicion: existen registros de peso.
- Pasos:
  1. Ingresar a /student/progress.
- Resultado esperado:
  - Se visualiza historial de peso y cambio.

### Mensajes
TC-STU-40 Ver conversacion
- Pasos:
  1. Ingresar a /student/messages.
- Resultado esperado:
  - Se muestra conversacion del alumno.
  - Mensajes se marcan como leidos al visualizar.

TC-STU-41 Enviar mensaje valido
- Pasos:
  1. Escribir mensaje <= 5000 caracteres.
  2. Enviar.
- Resultado esperado:
  - Mensaje enviado y limpiado el input.

TC-STU-42 Enviar mensaje vacio
- Pasos:
  1. Enviar sin contenido.
- Resultado esperado:
  - Error de validacion.

### Pagos e invoices
TC-STU-50 Ver pagina de pagos
- Pasos:
  1. Ingresar a /student/payments.
- Resultado esperado:
  - Se muestran metodos habilitados y factura pendiente si existe.

TC-STU-51 Pagar con Mercado Pago habilitado
- Precondicion: mercadopago habilitado y access_token configurado.
- Pasos:
  1. Iniciar pago Mercado Pago.
- Resultado esperado:
  - Se redirige al link de pago.

TC-STU-52 Pago con Mercado Pago deshabilitado
- Precondicion: mercadopago sin configurar.
- Pasos:
  1. Intentar pagar con Mercado Pago.
- Resultado esperado:
  - Se muestra error de configuracion.

TC-STU-53 Callback de Mercado Pago aprobado
- Precondicion: retorno con external_reference INV-{id} y status=approved.
- Pasos:
  1. Ingresar a /student/payments-callback con parametros de MP.
- Resultado esperado:
  - Invoice marcada como paid.
  - Mensaje de exito.

TC-STU-55 Callback de Mercado Pago pending
- Precondicion: retorno con external_reference INV-{id} y status=pending.
- Pasos:
  1. Ingresar a /student/payments-callback con parametros de MP.
- Resultado esperado:
  - Se muestra mensaje de pago en proceso.
  - La invoice permanece pendiente.

TC-STU-56 Callback de Mercado Pago rechazado
- Precondicion: retorno con external_reference INV-{id} y status distinto de approved/pending.
- Pasos:
  1. Ingresar a /student/payments-callback con parametros de MP.
- Resultado esperado:
  - Se muestra mensaje de error.
  - La invoice permanece pendiente.

TC-STU-57 Callback sin external_reference valido
- Precondicion: retorno sin external_reference o con formato distinto de INV-{id}.
- Pasos:
  1. Ingresar a /student/payments-callback con parametros incompletos.
- Resultado esperado:
  - No se marca ninguna invoice.
  - Se mantiene el estado actual.

TC-STU-58 Callback con invoice inexistente
- Precondicion: external_reference INV-{id} no existente.
- Pasos:
  1. Ingresar a /student/payments-callback con INV-{id} inexistente.
- Resultado esperado:
  - No se actualiza ninguna invoice.

TC-STU-54 Ver historial de invoices
- Pasos:
  1. Ingresar a /student/invoices.
- Resultado esperado:
  - Se muestra listado paginado de invoices.

### Plan detail
TC-STU-60 Ver detalle de plan propio
- Precondicion: assignment pertenece al alumno.
- Pasos:
  1. Ingresar a /student/plan/{assignment}.
- Resultado esperado:
  - Se muestra detalle del plan y ejercicios.

TC-STU-61 Bloqueo de plan ajeno
- Precondicion: assignment de otro alumno.
- Pasos:
  1. Ingresar a /student/plan/{assignment}.
- Resultado esperado:
  - Respuesta 404.

TC-STU-62 Descargar plan
- Pasos:
  1. Descargar desde /student/plan/{assignment}/download.
- Resultado esperado:
  - Se descarga el PDF del plan.
