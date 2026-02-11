# Casos de prueba - Pagos y Facturas (Tenant)

Fecha: 2026-02-11

## Alcance
Casos de prueba para facturas y pagos en el panel del entrenador:
- Listado y filtros de facturas
- Alta de factura manual
- Marcar pago manual
- Marcar vencida y cancelar

## Rutas y componentes
- Listado: /dashboard/billing/invoices (InvoicesIndex)
- Crear factura: /dashboard/billing/invoices/create/{student?} (InvoiceForm)

## Precondiciones
- Usuario con rol Admin/Asistente/Entrenador autenticado en tenant.
- Alumno existente.
- Plan comercial con pricing configurado (para auto-importe).

## Datos de prueba sugeridos
- Alumno: "Juan Perez" con plan y billing_frequency mensual
- Importe manual: 15000
- Vencimiento: hoy + 7 dias

## Casos de prueba

### Listado y filtros
TC-TEN-PF-01 Ver listado de facturas
- Pasos:
  1. Ingresar a /dashboard/billing/invoices.
- Resultado esperado:
  - Se muestra listado paginado de facturas.

TC-TEN-PF-02 Buscar por alumno
- Pasos:
  1. Buscar por nombre o email del alumno.
- Resultado esperado:
  - Se filtran facturas del alumno.

TC-TEN-PF-03 Filtrar por estado
- Pasos:
  1. Seleccionar estado (pending, paid, overdue, cancelled).
- Resultado esperado:
  - Se listan solo facturas con ese estado.

TC-TEN-PF-04 Filtrar por metodo de pago
- Pasos:
  1. Seleccionar metodo de pago.
- Resultado esperado:
  - Se listan solo facturas con ese metodo.

TC-TEN-PF-05 Filtrar por vencimiento
- Pasos:
  1. Definir rango dueFrom y dueTo.
- Resultado esperado:
  - Se listan facturas dentro del rango.

TC-TEN-PF-06 Limpiar filtros
- Pasos:
  1. Usar limpiar filtros.
- Resultado esperado:
  - Se restablece el listado.

### Alta de factura
TC-TEN-PF-10 Alta con auto-importe
- Precondicion: alumno con plan y pricing.
- Pasos:
  1. Seleccionar alumno.
  2. Dejar auto-importe activo.
  3. Guardar.
- Resultado esperado:
  - Se crea factura con importe del plan.

TC-TEN-PF-11 Auto-importe sin plan
- Precondicion: alumno sin plan o sin pricing.
- Pasos:
  1. Seleccionar alumno.
  2. Dejar auto-importe activo.
  3. Guardar.
- Resultado esperado:
  - Error en importe (invoices.no_plan_amount) y auto-importe desactivado.

TC-TEN-PF-12 Alta con importe manual
- Pasos:
  1. Desactivar auto-importe.
  2. Ingresar importe valido.
  3. Guardar.
- Resultado esperado:
  - Se crea factura con importe manual.

TC-TEN-PF-13 Validaciones de factura
- Pasos:
  1. Dejar alumno sin seleccionar.
  2. Usar importe < 1.
  3. Ingresar notes > 500.
- Resultado esperado:
  - Errores de validacion correspondientes.

TC-TEN-PF-14 Volver al listado
- Pasos:
  1. Guardar con back activo.
- Resultado esperado:
  - Redireccion a listado de facturas.

### Pago manual
TC-TEN-PF-20 Registrar pago manual
- Precondicion: factura pending.
- Pasos:
  1. Abrir modal de pago manual.
  2. Completar metodo y fecha.
  3. Guardar.
- Resultado esperado:
  - Factura marcada como paid.
  - Se guarda payment_method y paid_at.

TC-TEN-PF-21 Pago manual con notas
- Pasos:
  1. Ingresar notas y referencia.
  2. Guardar.
- Resultado esperado:
  - Se guarda meta.payment_notes y referencia.

TC-TEN-PF-22 Pago manual sobre factura no pendiente
- Precondicion: factura paid o cancelled.
- Pasos:
  1. Intentar registrar pago manual.
- Resultado esperado:
  - No se permite la accion.

### Estado de factura
TC-TEN-PF-30 Marcar como vencida
- Precondicion: factura pending.
- Pasos:
  1. Marcar como overdue.
- Resultado esperado:
  - Factura cambia a overdue.

TC-TEN-PF-31 Cancelar factura
- Precondicion: factura pending.
- Pasos:
  1. Cancelar factura.
- Resultado esperado:
  - Factura cambia a cancelled.

## Notas
- Los filtros reinician paginacion al cambiar.
- El listado usa paginacion de 10 por pagina.
- Pagos legacy redirigen a facturas (ruta /dashboard/payments).
