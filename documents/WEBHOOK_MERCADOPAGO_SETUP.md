# 🔗 Webhook Mercado Pago - Configuración para el Futuro

## Resumen

Se ha agregado una **URL de Webhook única por tenant** en la sección de Configuración General. Esta URL es necesaria para que Mercado Pago notifique automáticamente cuando hay un pago.

## URL del Webhook

Cada tenant tiene su propia URL:
```
https://{tenant_domain}/webhooks/mercadopago
```

**Ejemplo para sabrina:**
```
https://sabrina.fittrack.test/webhooks/mercadopago
```

## Dónde Está

En el panel de cada tenant:
- **Panel Admin** → **Configuración** → **Configuración General**
- **Sección:** "Integración Mercado Pago - Webhook"
- Hay un botón **"Copiar URL"** para facilitar la tarea

## Cómo Funciona

### Sistema Actual (Desarrollo)
✅ **Verificación al retornar** - Cuando el usuario vuelve de Mercado Pago, se verifica automáticamente

### Sistema Futuro (Webhooks)
✅ **Notificaciones automáticas** - Mercado Pago notifica cuando hay un pago
- No depende de que el usuario esté en la página
- Más robusto y confiable
- Funciona si el usuario cierra la ventana

## Paso a Paso: Configurar Webhooks en Producción

### 1. Obtén la URL
En tu panel de FitTrack:
1. Ve a **Configuración General**
2. Busca **"Integración Mercado Pago - Webhook"**
3. Click en **"Copiar URL"**

Ejemplo:
```
https://sabrina.fittrack.test/webhooks/mercadopago
```

### 2. Configura en Mercado Pago
1. Ve a https://www.mercadopago.com.ar/developers/panel
2. Selecciona **"Notificaciones"** en el menú
3. Elige **"Webhooks (para IPN)"**
4. Pega la URL copiada
5. Selecciona los eventos:
   - ✅ `payment.created`
   - ✅ `payment.updated`
6. Guarda

### 3. Prueba
1. Haz un pago de prueba
2. Verifica que Mercado Pago muestre "Entregado" junto a la notificación

## Código del Webhook

El controlador ya está implementado en:
```
app/Http/Controllers/Tenant/MercadoPagoWebhookController.php
```

**Qué hace:**
```php
POST /webhooks/mercadopago
├─ Recibe notificación de Mercado Pago
├─ Extrae el preference_id y payment_id
├─ Consulta la API de Mercado Pago
├─ Obtiene el status del pago (approved/pending/rejected)
├─ Si es 'approved' → Marca invoice como 'paid'
├─ Si es 'pending' → Mantiene como pendiente
├─ Si es rechazado → Revierte a pendiente
└─ Retorna HTTP 200 (OK)
```

## Notificación de Mercado Pago

Cuando configures el webhook, Mercado Pago enviará:

```json
{
  "type": "payment",
  "action": "payment.updated",
  "data": {
    "id": "12345678"
  }
}
```

El controlador procesará esto automáticamente.

## En Desarrollo Local

Para testear webhooks en desarrollo, puedes usar **ngrok**:

```bash
# Instalar ngrok (primera vez)
brew install ngrok  # macOS
# o descargar de https://ngrok.com

# Iniciar ngrok
ngrok http 8000

# Te mostrará algo como:
# https://abc123.ngrok.io -> http://localhost:8000

# Entonces la URL del webhook sería:
# https://abc123.ngrok.io/sabrina/webhooks/mercadopago
# (ajusta según tu ruta)

# Registra esa URL en Mercado Pago
```

## Configuración Recomendada

| Aspecto | Desarrollo | Producción |
|---------|-----------|-----------|
| **Verificación al retornar** | ✅ Sí (actual) | ✅ Sí (respaldo) |
| **Webhooks** | ⚠️ Opcional con ngrok | ✅ Sí (principal) |
| **URL del webhook** | `localhost` + ngrok | Tu dominio de prod |
| **Token MP** | Sandbox (APP_USR_) | Producción |

## Script para Revisar el Webhook

```bash
# Ver si la ruta existe
php artisan route:list | grep webhook

# Ver el controlador
cat app/Http/Controllers/Tenant/MercadoPagoWebhookController.php

# Ver logs de webhooks
php artisan pail | grep -i webhook
```

## Variables Generadas Automáticamente

En el componente `General.php`:
```php
// Se genera automáticamente con el dominio del tenant
public function generateWebhookUrl(): string {
    $tenantDomain = tenant()->getDomain();
    // ej: sabrina.fittrack.test
    
    return "https://{$tenantDomain}/webhooks/mercadopago";
}
```

## Flujo de Webhook vs Verificación al Retornar

```
COMPARACIÓN:

┌─ VERIFICACIÓN AL RETORNAR (Actual) ─┐
│ Usuario → MP → Paga → Vuelve        │
│ Página verifica: ¿Fue aprobado?     │
│ Si sí → Marca como pagado           │
│ ⚠️ Depende de que vuelva a la página│
└────────────────────────────────────┘

┌─ WEBHOOK (Futuro) ─────────────────┐
│ Usuario → MP → Paga → MP notifica   │
│ (sin que el usuario tenga que volver)│
│ POST /webhooks/mercadopago          │
│ Marca como pagado automáticamente   │
│ ✅ Independiente del usuario        │
└────────────────────────────────────┘
```

## Testing de Webhook

### Con Postman/Thunder Client
```http
POST https://sabrina.fittrack.test/webhooks/mercadopago
Content-Type: application/json

{
  "type": "payment",
  "action": "payment.updated",
  "data": {
    "id": "1234567890"
  }
}
```

### Con curl
```bash
curl -X POST https://sabrina.fittrack.test/webhooks/mercadopago \
  -H "Content-Type: application/json" \
  -d '{"type":"payment","action":"payment.updated","data":{"id":"1234567890"}}'
```

## Seguridad

⚠️ **Importante:** El webhook NO requiere autenticación actualmente para mantenerlo simple, pero Mercado Pago incluye una firma en el header `X-Signature` que podrías validar opcionalmente:

```php
// Opcional: validar firma
$signature = request()->header('X-Signature');
// Validar que venga de Mercado Pago
```

## Próximos Pasos

1. ✅ Estructura de webhook implementada
2. ✅ URL única por tenant configurada
3. ✅ Botón de copiar URL en configuración
4. 📋 Cuando pases a producción:
   - Registra la URL en Mercado Pago
   - Testea con ngrok en desarrollo si quieres
   - El webhook funcionará automáticamente

## Referencia

- **Documentación Mercado Pago Webhooks:** https://www.mercadopago.com.ar/developers/es/docs/checkout-pro/webhooks/overview
- **Mi Controlador:** `app/Http/Controllers/Tenant/MercadoPagoWebhookController.php`
- **Mi Ruta:** `routes/tenant.php` línea ~90
- **Mi Configuración:** `app/Livewire/Tenant/Configuration/General.php`
