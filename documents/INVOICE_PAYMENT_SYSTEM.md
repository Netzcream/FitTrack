# Sistema de Invoices y Pagos con Mercado Pago

## Descripción General

Sistema completo de facturación (invoices) y pagos integrado con Mercado Pago para FitTrack. Permite generar invoices para alumnos basados en su plan comercial, procesar pagos mediante Mercado Pago, y recibir notificaciones automáticas del estado de los pagos.

## Arquitectura

### Modelos

#### Invoice (`app/Models/Tenant/Invoice.php`)
Representa una factura/cobro para un alumno.

**Campos principales:**
- `student_id` - Alumno asociado
- `plan_assignment_id` - Plan asignado (opcional)
- `amount` - Monto a cobrar
- `status` - Estado: `pending`, `paid`, `overdue`, `cancelled`
- `due_date` - Fecha de vencimiento
- `paid_at` - Fecha de pago
- `payment_method` - Método usado: `mercadopago`, `transfer`, `cash`
- `external_reference` - ID de la transacción en Mercado Pago
- `meta` - JSON con metadatos (plan_name, billing_frequency, currency, label)

**Relaciones:**
- `student()` - Alumno
- `planAssignment()` - Plan asignado

**Scopes:**
- `pending()` - Invoices pendientes u overdue
- `paid()` - Invoices pagados
- `overdue()` - Invoices vencidos

**Accessors:**
- `is_pending` - Booleano si está pendiente
- `is_paid` - Booleano si está pagado
- `is_overdue` - Booleano si está vencido
- `formatted_amount` - Monto formateado con moneda

#### Student - Nuevas relaciones
- `invoices()` - Todos los invoices
- `pendingInvoices()` - Invoices pendientes ordenados por vencimiento
- `paidInvoices()` - Invoices pagados ordenados por fecha de pago

### Servicios

#### InvoiceService (`app/Services/Tenant/InvoiceService.php`)

**Métodos principales:**

```php
// Crear invoice para un alumno basado en su plan comercial
createForStudent(Student $student, ?StudentPlanAssignment $planAssignment = null, ?Carbon $dueDate = null): Invoice

// Marcar invoice como pagado
markAsPaid(Invoice $invoice, string $paymentMethod, ?string $externalReference = null): Invoice

// Marcar invoice como vencido
markAsOverdue(Invoice $invoice): Invoice

// Cancelar invoice
cancel(Invoice $invoice): Invoice

// Obtener invoices pendientes de un alumno
getPendingForStudent(Student $student): Collection

// Obtener la próxima invoice pendiente
getNextPendingForStudent(Student $student): ?Invoice

// Verificar si tiene invoices vencidos
hasOverdueInvoices(Student $student): bool

// Actualizar todos los invoices vencidos (para comando cron)
updateOverdueInvoices(): int
```

#### MercadoPagoService (`app/Services/Tenant/Payments/MercadoPagoService.php`)

**Método principal:**

```php
// Crear link de pago para un invoice
createInvoicePaymentLink(Invoice $invoice): string
```

**Características:**
- Inicializa SDK de Mercado Pago con token del tenant
- Genera preferencia de pago con datos del invoice
- Configura URLs de retorno
- Registra external_reference como `INV-{invoice_id}`
- Incluye email del alumno como payer

### Controladores

#### PaymentController (`app/Http/Controllers/Tenant/PaymentController.php`)

```php
// Crear invoice y generar link de pago
POST /tenant/payments/create-invoice
{
  "student_id": 123
}
```

#### MercadoPagoWebhookController (`app/Http/Controllers/Tenant/MercadoPagoWebhookController.php`)

**Endpoint:** `POST /tenant/webhooks/mercadopago`

**Función:** Recibir notificaciones de Mercado Pago sobre cambios de estado en pagos.

**Flujo:**
1. Recibe notificación de Mercado Pago
2. Valida que sea notificación de pago
3. Consulta estado del pago en API de Mercado Pago
4. Extrae `external_reference` (formato `INV-{id}`)
5. Busca el invoice correspondiente
6. Actualiza estado según el estado del pago:
   - `approved` → marca invoice como `paid`
   - `pending`, `in_process`, `in_mediation` → mantiene `pending`
   - `rejected`, `cancelled`, `refunded`, `charged_back` → revierte a `pending` si estaba `paid`

**Configuración en Mercado Pago:**
Ir a tu cuenta de Mercado Pago → Integraciones → Webhooks y configurar:
```
URL: https://tudominio.fittrack.test/webhooks/mercadopago
Eventos: Pagos
```

### Livewire Components

#### Student/Payments (`app/Livewire/Tenant/Student/Payments.php`)

**Vista para el alumno:** Muestra métodos de pago disponibles y permite pagar con Mercado Pago.

**Características:**
- Carga invoice pendiente del alumno
- Muestra estado del pago (pendiente, vencido, al día)
- Botón de pago con Mercado Pago
- Manejo de errores

**Método principal:**
```php
public function payWithMercadoPago(): void
```

**Flujo:**
1. Busca invoice pendiente del alumno
2. Si no existe, crea uno nuevo
3. Genera link de pago con MercadoPagoService
4. Redirige al alumno a Mercado Pago

**Vista:** `resources/views/livewire/tenant/student/payments.blade.php`

Muestra:
- Estado del pago (pendiente/vencido/al día)
- Monto y fecha de vencimiento del invoice
- Métodos de pago disponibles (transferencia, efectivo, Mercado Pago)
- Botón para pagar con Mercado Pago

### Comandos Artisan

#### UpdateOverdueInvoices

```bash
php artisan invoices:update-overdue
```

**Función:** Actualiza todos los invoices con `status=pending` y `due_date` pasado a `status=overdue`.

**Uso:** Ejecutar diariamente en cron para mantener actualizados los estados.

**Configuración en `app/Console/Kernel.php`:**
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('invoices:update-overdue')->daily();
}
```

## Flujo Completo de Pago

### 1. Alumno quiere pagar

1. Entra a `/student/payments`
2. Ve su invoice pendiente (si existe) o el sistema crea uno
3. Hace clic en "Pagar con Mercado Pago"

### 2. Generación del link de pago

1. Livewire llama a `payWithMercadoPago()`
2. InvoiceService busca o crea invoice
3. MercadoPagoService genera preferencia de pago
4. Redirige al alumno a Mercado Pago

### 3. Alumno paga en Mercado Pago

1. Completa el pago en el sitio de Mercado Pago
2. Mercado Pago redirige al alumno de vuelta a `/student/payments`

### 4. Notificación de pago (webhook)

1. Mercado Pago envía webhook a `/webhooks/mercadopago`
2. Webhook consulta estado del pago en API de Mercado Pago
3. Busca invoice por `external_reference`
4. Marca invoice como `paid` si el pago fue aprobado

### 5. Confirmación

1. Alumno ve mensaje "Plan al día" en `/student/payments`
2. Trainer ve el pago registrado en dashboard

## Configuración Inicial

### 1. Variables de entorno

Ya están configuradas en `.env`:

```env
MERCADOPAGO_BASE_URL=https://api.mercadopago.com
MERCADOPAGO_RUNTIME_ENV=local
MERCADOPAGO_PUBLIC=APP_USR-deda02ca-ef71-40c9-ab31-abfe3c165690
MERCADOPAGO_ACCESS_TOKEN=APP_USR-7840376489848978-011222-859b20c29f1eafbc1e95ef2508d65e9c-3129321289
```

### 2. Configuración del tenant

Cada tenant debe configurar su access token en:
- Dashboard → Configuración → General → Métodos de Pago → Mercado Pago
- Habilitar checkbox "Aceptar Mercado Pago"
- Ingresar Access Token

El sistema usa `tenant_config('payment_mp_access_token')` para obtener el token.

### 3. Configurar webhook en Mercado Pago

Para producción:
1. Ir a https://www.mercadopago.com.ar/developers/panel/app
2. Seleccionar tu aplicación
3. Ir a "Webhooks"
4. Agregar nueva URL: `https://tudominio.com/webhooks/mercadopago`
5. Seleccionar eventos: "Pagos"

Para desarrollo/testing:
- Usar ngrok o similar para exponer tu localhost
- Configurar la URL del túnel en Mercado Pago

### 4. Ejecutar migraciones

```bash
php artisan tenants:migrate
```

## Testing

### Crear invoice manualmente

```php
use App\Services\Tenant\InvoiceService;
use App\Models\Tenant\Student;

$student = Student::first();
$invoiceService = new InvoiceService();
$invoice = $invoiceService->createForStudent($student);

dd($invoice);
```

### Simular pago

```php
$invoiceService->markAsPaid($invoice, 'mercadopago', 'TEST-PAYMENT-123');
```

### Ver invoices de un alumno

```php
$student = Student::first();
$pending = $student->pendingInvoices;
$paid = $student->paidInvoices;
```

### Probar webhook localmente

```bash
curl -X POST http://fittrack.test/webhooks/mercadopago \
  -H "Content-Type: application/json" \
  -d '{
    "type": "payment",
    "action": "payment.updated",
    "data": {
      "id": "1234567890"
    }
  }'
```

## Próximos Pasos / Mejoras

1. **Invoices automáticos por vencimiento de plan**
   - Crear invoice cuando `plan_assignment.ends_at` se aproxima
   - Enviar notificación al alumno

2. **Historial de pagos en dashboard del alumno**
   - Vista con todos los invoices (pendientes y pagados)
   - Descargar comprobantes

3. **Dashboard para trainer**
   - Ver invoices pendientes por alumno
   - Marcar como pagado manualmente (efectivo/transferencia)
   - Estadísticas de pagos

4. **Notificaciones automáticas**
   - Email cuando se genera invoice
   - Email cuando se aprueba pago
   - Recordatorio de invoice por vencer

5. **Suscripciones con Mercado Pago**
   - Usar Mercado Pago Subscriptions para pagos recurrentes
   - Cobro automático mensual/trimestral/anual

6. **Reportes**
   - Ingresos por mes
   - Alumnos con pagos pendientes
   - Tasa de morosidad

## Troubleshooting

### El webhook no recibe notificaciones

1. Verificar que la URL esté correctamente configurada en Mercado Pago
2. Revisar logs: `storage/logs/laravel.log`
3. Verificar que el servidor sea accesible públicamente
4. Probar con ngrok en desarrollo

### Invoice no se marca como pagado

1. Revisar logs del webhook
2. Verificar que `external_reference` sea correcto (formato `INV-{id}`)
3. Consultar el pago en Mercado Pago API manualmente

### Error al generar link de pago

1. Verificar que el tenant tenga access token configurado
2. Verificar que el access token sea válido
3. Revisar que el alumno tenga plan comercial con pricing configurado

### Alumno no puede pagar

1. Verificar que Mercado Pago esté habilitado en configuración del tenant
2. Verificar que el alumno tenga plan comercial asignado
3. Verificar que el plan tenga pricing configurado para su billing_frequency

## Logs Importantes

El sistema registra eventos clave:

- Creación de preferencia de pago
- Recepción de webhooks
- Cambios de estado de invoices
- Errores de API de Mercado Pago

Buscar en `storage/logs/laravel.log`:
```
MercadoPago preference payload
MercadoPago webhook received
Invoice marked as paid
Error processing MercadoPago webhook
```

## Seguridad

- Webhook endpoint no requiere autenticación (Mercado Pago no envía headers de auth)
- Se valida el external_reference para evitar modificar invoices incorrectos
- Se consulta el estado del pago directamente en API de Mercado Pago para evitar falsificación
- Access tokens se almacenan en configuración del tenant (aislados por tenant)
- Los invoices están aislados por tenant (multi-tenancy)

## Contacto y Soporte

Para dudas sobre la implementación, revisar:
- Documentación oficial de Mercado Pago: https://www.mercadopago.com.ar/developers
- SDK PHP de Mercado Pago: https://github.com/mercadopago/sdk-php
