# Configurar Auto-Return en Mercado Pago

## El Problema
Los usuarios completan el pago exitosamente, pero Mercado Pago no los redirige automáticamente de vuelta a FitTrack.

## La Solución

### Opción 1: Auto-Return (RECOMENDADO)
En Mercado Pago, existe una opción de **Auto-Return** que redirige automáticamente después del pago:

**Pasos:**
1. Ve a tu cuenta de Mercado Pago: https://www.mercadopago.com/settings/profile
2. Ve a **Configuraciones** → **Integraciones**
3. Busca la sección **"Auto-return"** o **"Volver automáticamente a mi sitio"**
4. Habilita esta opción
5. Especifica la URL: `https://sabrina.fittrack.test/student/payments` (o la tuya)

### Opción 2: Payment `auto_return` en Preferencia
En algunos casos, necesitas agregar `auto_return` al payload de la preferencia:

```php
'auto_return' => 'approved',  // Redirige tras pago exitoso
```

### Opción 3: Usar IPN/Webhook (Para Producción)
En producción, configura un webhook para recibir notificaciones y redirigir al usuario:

1. Configurar webhook en Mercado Pago
2. Tu app recibe notificación de `payment.completed`
3. Tu app redirige al usuario

## Verificar las Back URLs en tu Preferencia

Para asegurar que estamos enviando las back_urls correctamente:

```bash
# En tu app, revisar los logs:
tail -f storage/logs/laravel.log | grep MercadoPago
```

Deberías ver algo como:
```json
{
  "back_urls": {
    "success": "https://sabrina.fittrack.test/student/payments",
    "failure": "https://sabrina.fittrack.test/student/payments",
    "pending": "https://sabrina.fittrack.test/student/payments"
  },
  "has_back_urls": true
}
```

## Test en Sandbox

Para probar pagos en sandbox sin que Mercado Pago se cuelgue:

1. **Tarjeta de prueba exitosa:**
   - Número: 4509 9535 6623 3704
   - Exp: 11/25
   - CVC: 123

2. Luego de pagar, deberías ver:
   - ✅ Mensaje de pago exitoso
   - ✅ Opción de "Volver a la tienda" (botón o automático)
   - ✅ Redirección a tu URL

## El Código Ya Está Preparado

Tu `MercadoPagoService.php` ahora:
✅ Envía `back_urls` correctamente
✅ Loguea el payload completo
✅ Sin validación restrictiva de URLs

## Próximo Paso

1. Habilita **Auto-Return** en tu cuenta de Mercado Pago
2. Reinicia el servidor Laravel: `php artisan serve`
3. Intenta pagar de nuevo
4. El usuario debería regresar automáticamente

## Si Aún No Funciona

Agrega esto al payload en `MercadoPagoService.php`:

```php
$payload = [
    // ... existing code ...
    'auto_return' => 'approved',
    'back_urls' => [
        'success' => $backUrl,
        'failure' => $backUrl,
        'pending' => $backUrl,
    ],
];
```

Luego intenta de nuevo.
