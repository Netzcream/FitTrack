# ✅ MERCADO PAGO - PAGO AUTOMÁTICO IMPLEMENTADO

## Resumen de la Solución

El sistema ahora **verifica automáticamente y marca como pagado** el invoice cuando el usuario retorna de Mercado Pago.

### Antes (Sin verificación automática)
```
1. Usuario hace click en "Pagar"
2. Va a Mercado Pago
3. Realiza el pago exitosamente
4. Vuelve a la app
5. ❌ El invoice sigue mostrando "Pendiente"
```

### Ahora (Con verificación automática)
```
1. Usuario hace click en "Pagar"
2. Va a Mercado Pago
3. Realiza el pago exitosamente
4. Vuelve a la app
5. ✅ Automáticamente:
   - Se verifica el pago en MP
   - Se marca como "paid"
   - Muestra "Plan al día"
   - Página se recarga
```

---

## Flujo Técnico

### 1️⃣ Usuario Paga y Retorna
- Mercado Pago redirige a `/student/payments`
- JavaScript en la página detecta que hay un invoice con `external_reference`
- Dispara evento Livewire `paymentCheck`

### 2️⃣ Livewire Verifica
- `Payments::checkPendingPayment()` es llamado
- Hace POST a `/student/payments/verify-mercadopago`
- Envía el `invoice_id`

### 3️⃣ Controlador Verifica en Mercado Pago
```php
PaymentController::verifyMercadoPago()
├─ Obtiene el invoice
├─ Lee el preference_id del external_reference
├─ Consulta API de Mercado Pago
├─ Si status == 'approved'
│  └─ Marca invoice como 'paid'
└─ Retorna {status: 'paid'}
```

### 4️⃣ Página se Recarga
- JavaScript escucha evento `payment-verified`
- Recarga la página automáticamente
- Usuario ve "Plan al día" ✅

---

## Archivos Modificados

### 1. `app/Http/Controllers/Tenant/PaymentController.php`
**Nuevos métodos:**
```php
// Verificar pago cuando retorna de MP
public function verifyMercadoPago(Request $request, InvoiceService $invoiceService)

// Consultar estado en API de Mercado Pago
private function checkMercadoPagoPayment(string $preferenceId): ?string
```

### 2. `app/Livewire/Tenant/Student/Payments.php`
**Nuevas características:**
```php
// Al cargar la página
public function mount()

// Escuchar evento desde JavaScript
#[On('paymentCheck')]
public function checkPendingPayment(): void
```

### 3. `routes/tenant-student.php`
**Nueva ruta:**
```php
Route::post('/payments/verify-mercadopago', [PaymentController::class, 'verifyMercadoPago'])
    ->name('verify-mercadopago');
```

### 4. `resources/views/livewire/tenant/student/payments.blade.php`
**Script agregado:**
```javascript
// Detecta retorno de Mercado Pago y verifica automáticamente
// Recarga la página cuando se completa el pago
```

---

## Cómo Funciona

### 1. Crear Payment Link
```php
// MercadoPagoService
$preference = $client->create($payload);
$invoice->update([
    'external_reference' => $preference->id  // Preference ID
]);
return $preference->init_point;  // URL para pagar
```

### 2. Verificar Pago
```php
// PaymentController
$response = $client->get(
    "https://api.mercadopago.com/v1/checkout/preferences/{$preferenceId}",
    ['headers' => ['Authorization' => "Bearer {$token}"]]
);

$payments = $response['payments'];
$lastPayment = end($payments);
return $lastPayment['status'];  // 'approved', 'pending', 'failed'
```

### 3. Actualizar Invoice
```php
// InvoiceService
if ($paymentStatus === 'approved') {
    $invoiceService->markAsPaid($invoice, 'mercadopago', $preferenceId);
}
```

---

## Testing (Paso a Paso)

### ✅ Caso de Éxito

```
1. Ve a /student/payments
2. Click en "Pagar con Mercado Pago"
3. Login con test_user:
   Email: test_user_XXXXX@testuser.com (del panel de MP)
   
4. Completa el pago:
   Tarjeta: 4111 1111 1111 1111
   Fecha: 12/25
   CVC: 123
   
5. Click "Volver a la tienda"
6. Espera 2-3 segundos
7. ¡La página se recarga automáticamente!
8. Verás: "Plan al día" ✅
```

### 📋 Qué Pasa en Background

```
Payload enviado a Mercado Pago:
{
  "items": [{
    "title": "Plan de entrenamiento",
    "quantity": 1,
    "unit_price": 8000,
    "currency_id": "ARS"
  }],
  "external_reference": "INV-123",
  "payer": {"email": "test_user@testuser.com"},
  "back_urls": {"success": "...", "failure": "...", "pending": "..."}
}

Retorno:
{
  "id": "12345-a53d362e-e826-...",  ← preference_id
  "init_point": "https://www.mercadopago.com.ar/checkout/..."
}
```

```
Verificación al retornar:
GET /v1/checkout/preferences/12345-a53d362e-e826-...
Authorization: Bearer APP_USR-7840376489848978...

Response:
{
  "payments": [{
    "status": "approved",  ← ¡Pagado!
    ...
  }]
}

Invoice actualizado:
status: 'paid'
paid_at: 2026-01-13 15:45:22
payment_method: 'mercadopago'
```

---

## Variables de Entorno

```env
MERCADOPAGO_RUNTIME_ENV=sandbox
```

Verificar en tenant:
```bash
php artisan tinker
>>> tenant_config('payment_mp_access_token')
>>> tenant_config('payment_mp_public_key')
>>> tenant_config('payment_accepts_mercadopago')
```

---

## Logs

Para ver qué está pasando:
```bash
# Ver logs en tiempo real
php artisan pail | grep -i "mercadopago\|payment\|preference"

# Ver archivo de log
tail -f storage/logs/laravel.log | grep -i mercadopago
```

Logs importantes:
- `MercadoPago preference payload` - Qué se envía a MP
- `MercadoPago API Exception` - Errores en creación de preference
- `Error verifying Mercado Pago payment` - Errores al verificar
- `Error fetching Mercado Pago preference` - Errores al consultar API

---

## Casos de Error Manejados

✅ **Token no configurado**
- Mensaje claro: "Mercado Pago no esta configurado"

✅ **Email no es test_user**
- Error: "Una de las partes con la que intentás hacer el pago es de prueba"
- Solución: Usar email del test_user del panel de MP

✅ **Invoice sin preference**
- No intenta verificar
- Muestra estado "Pendiente"

✅ **Error de conexión con MP**
- Se loguea el error
- No afecta al usuario
- Puede intentar de nuevo

✅ **Pago rechazado**
- Status != 'approved'
- Invoice sigue como 'pending'
- Usuario puede reintentar

---

## Próximos Pasos Opcionales

### 1. **Agregar Webhooks de Mercado Pago**
Para producción, usar webhooks además de verificación:
- Mercado Pago notifica automáticamente
- Más robusto que polling

### 2. **Botón de Verificación Manual**
En caso de que la verificación automática falle:
```blade
<flux:button wire:click="checkPendingPayment">
  Verificar Pago
</flux:button>
```

### 3. **Notificaciones por Email**
Notificar al usuario cuando el pago se confirma:
```php
$student->notify(new PaymentConfirmed($invoice));
```

---

## Diagrama del Flujo

```
┌─────────────────────────────────────────────────────┐
│ Usuario en /student/payments                        │
│ Click: "Pagar con Mercado Pago"                    │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ payWithMercadoPago()                               │
│ ├─ Crea/obtiene Invoice                           │
│ ├─ Llama MercadoPagoService                        │
│ └─ Obtiene URL de pago                            │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ MercadoPagoService::createInvoicePaymentLink()     │
│ ├─ Prepara payload                                 │
│ ├─ Llama PreferenceClient::create()               │
│ ├─ Guarda preference_id en invoice->external_ref  │
│ └─ Retorna init_point                             │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ $this->redirect($url)                              │
│ Redirige a: https://mercadopago.com.ar/checkout/..│
└────────────────┬────────────────────────────────────┘
                 │
          [USUARIO PAGA EN MERCADO PAGO]
          [COMPLETA LA TRANSACCIÓN]
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ Click: "Volver a la tienda"                        │
│ Retorna a: /student/payments                       │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ JavaScript detecta que hay invoice pendiente        │
│ Dispara: Livewire.dispatch('paymentCheck')         │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ checkPendingPayment() en Payments.php              │
│ POST /student/payments/verify-mercadopago          │
│ Envía: {invoice_id: 123}                           │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ PaymentController::verifyMercadoPago()             │
│ ├─ Obtiene invoice                                │
│ ├─ Lee preference_id                              │
│ └─ Consulta: /checkout/preferences/{id}           │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ MERCADO PAGO API                                   │
│ GET /v1/checkout/preferences/{preference_id}       │
│ Response: { payments: [{ status: 'approved' }] }   │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ Retorna status: 'approved'                          │
│ Llama: InvoiceService::markAsPaid()               │
│ ├─ invoice->status = 'paid'                       │
│ ├─ invoice->paid_at = now()                       │
│ └─ invoice->save()                                │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ Retorna: {status: 'paid'}                           │
│ JavaScript dispara: payment-verified               │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ location.reload()                                   │
│ Página se recarga automáticamente                   │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ Usuario ve: "Plan al día" ✅                        │
│ Invoice aparece en historial como "Pagado"         │
└─────────────────────────────────────────────────────┘
```

---

## Versión Anterior vs Actual

| Feature | Antes | Ahora |
|---------|-------|-------|
| Crear payment link | ✅ | ✅ |
| Verificar pago automático | ❌ | ✅ |
| Actualizar invoice | Manual | ✅ Automático |
| Webhook de MP | ❌ | Opcional |
| Error handling | Básico | Robusto |
| Logs detallados | ❌ | ✅ |

---

## Próximas Pruebas

```bash
# 1. Ir a http://sabrina.fittrack.test/student/payments

# 2. Hacer un pago completamente

# 3. Verificar logs
php artisan pail

# 4. Verificar BD
php artisan tinker
>>> Invoice::latest()->first()
>>> # status debe ser 'paid'

# 5. Verificar dashboard
# Debería mostrar "Plan al día"
```

¡Listo para usar! 🚀
