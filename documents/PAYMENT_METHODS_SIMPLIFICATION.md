# Sistema de Métodos de Pago - Simplificado

## Resumen de Cambios

Se simplificó el sistema de métodos de pago eliminando el ABM completo y trasladándolo a la configuración general del tenant.

### Antes
- Modelo `PaymentMethod` con CRUD completo
- Tabla `payment_methods` con soft deletes
- Componentes Livewire: `PaymentMethods\Index` y `PaymentMethods\Form`
- Rutas dedicadas para gestionar métodos de pago

### Ahora
- 3 métodos fijos configurables: Transferencia, Mercadopago y Efectivo
- Configuración directa en "Configuración General"
- Datos guardados en tabla `configurations`
- Helpers PHP para acceder fácilmente a la configuración

## Métodos de Pago Disponibles

### 1. Transferencia/Depósito Bancario
**Campos configurables:**
- ✅ Acepto Transferencia (checkbox)
- Nombre del Banco
- Titular de la Cuenta (opcional)
- CUIT/CUIL (opcional)
- CBU
- Alias

### 2. Mercadopago
**Campos configurables:**
- ✅ Acepto Mercadopago (checkbox)
- Access Token (para API)
- Public Key (opcional, para web)

### 3. Efectivo
**Campos configurables:**
- ✅ Acepto Efectivo (checkbox)
- Instrucciones (ej: "5% descuento antes del vencimiento")

## Claves de Configuración

Todas se guardan en la tabla `configurations`:

```
payment_accepts_transfer          (bool)
payment_bank_name                 (string)
payment_bank_account_holder       (string, opcional)
payment_bank_cuit_cuil            (string, opcional)
payment_bank_cbu                  (string)
payment_bank_alias                (string)

payment_accepts_mercadopago       (bool)
payment_mp_access_token           (string)
payment_mp_public_key             (string)

payment_accepts_cash              (bool)
payment_cash_instructions         (string)
```

## Uso en el Código

### Helpers Disponibles

#### 1. Obtener métodos aceptados
```php
$methods = accepted_payment_methods();
// Retorna: ['transfer', 'mercadopago', 'cash']
```

#### 2. Obtener configuración de un método
```php
$transferConfig = payment_method_config('transfer');
// Retorna:
// [
//   'enabled' => true,
//   'bank_name' => 'Banco Galicia',
//   'account_holder' => 'Juan Pérez',
//   'cuit_cuil' => '20-12345678-9',
//   'cbu' => '0000000000000000000000',
//   'alias' => 'MI.ALIAS.BANCO'
// ]

$mpConfig = payment_method_config('mercadopago');
$cashConfig = payment_method_config('cash');
```

#### 3. Verificar si acepta un método específico
```php
if (tenant_config('payment_accepts_transfer', false)) {
    // Mostrar opción de transferencia
}
```

### Ejemplo en Blade
```blade
@php
    $acceptedMethods = accepted_payment_methods();
@endphp

@if(in_array('transfer', $acceptedMethods))
    @php
        $config = payment_method_config('transfer');
    @endphp
    <div>
        <h3>Transferencia Bancaria</h3>
        <p>Banco: {{ $config['bank_name'] }}</p>
        <p>CBU: {{ $config['cbu'] }}</p>
        <p>Alias: {{ $config['alias'] }}</p>
    </div>
@endif

@if(in_array('mercadopago', $acceptedMethods))
    <button wire:click="payWithMercadopago">
        Pagar con Mercadopago
    </button>
@endif

@if(in_array('cash', $acceptedMethods))
    @php
        $config = payment_method_config('cash');
    @endphp
    <div>
        <h3>Efectivo</h3>
        <p>{{ $config['instructions'] }}</p>
    </div>
@endif
```

### Ejemplo en Livewire Component
```php
class PaymentForm extends Component
{
    public string $selectedMethod = '';
    public array $availableMethods = [];

    public function mount()
    {
        $this->availableMethods = accepted_payment_methods();
        $this->selectedMethod = $this->availableMethods[0] ?? '';
    }

    public function processPayment()
    {
        if ($this->selectedMethod === 'mercadopago') {
            $config = payment_method_config('mercadopago');
            $accessToken = $config['access_token'];
            // Procesar pago con Mercadopago
        }
    }
}
```

## Migración de Datos

La migración `2026_01_10_150000_migrate_payment_methods_to_config.php` se encarga de:

1. Leer los registros activos de `payment_methods`
2. Mapear cada código (TRANSFER, CARD/MERCADOPAGO, CASH) a las nuevas configuraciones
3. Guardar en la tabla `configurations`

**Nota:** Las instrucciones de transferencia existentes se registran en el log para revisión manual, ya que ahora hay campos estructurados (banco, CBU, alias).

## Archivos Modificados

### Componentes
- ✅ `app/Livewire/Tenant/Configuration/General.php` - Agregados campos de métodos de pago
- ✅ `resources/views/livewire/tenant/configuration/general.blade.php` - Agregada UI de métodos de pago

### Rutas
- ✅ `routes/tenant.php` - Eliminadas rutas de payment-methods

### Navegación
- ✅ `resources/views/components/layouts/tenant/sidebar.blade.php` - Eliminado ítem de menú

### Helpers
- ✅ `app/Support/TenantHelpers.php` - Agregados helpers para métodos de pago

### Migraciones
- ✅ `database/migrations/2026_01_10_150000_migrate_payment_methods_to_config.php` - Migración de datos
- ✅ `database/migrations/2026_01_10_180000_drop_payment_methods_table.php` - Eliminación de tabla payment_methods

## Archivos Deprecados (Eliminados)

La tabla `payment_methods` fue eliminada de la base de datos. Los siguientes archivos ya no se utilizan:

- `app/Models/Tenant/PaymentMethod.php`
- `app/Livewire/Tenant/PaymentMethods/Index.php`
- `app/Livewire/Tenant/PaymentMethods/Form.php`
- `resources/views/livewire/tenant/payment-methods/index.blade.php`
- `resources/views/livewire/tenant/payment-methods/form.blade.php`

**Nota:** Pueden eliminarse manualmente si lo deseas, ya que la tabla ha sido removida de la base de datos.

## Próximos Pasos

1. **Ejecutar migraciones en cada tenant**
   ```bash
   php artisan tenants:migrate
   ```

2. **Revisar logs** para instrucciones de transferencia que necesiten migración manual

3. **Actualizar componentes** que usen el modelo `PaymentMethod` para usar los nuevos helpers

4. **Actualizar módulo de pagos de estudiantes** para usar `accepted_payment_methods()`

5. **Testing:** Verificar que los alumnos vean correctamente los métodos de pago disponibles
