# 🔔 Sistema de Eventos y Notificaciones - FitTrack

## ✅ Implementación Completada

Se ha creado una arquitectura completa de eventos y listeners para manejar notificaciones en FitTrack.

---

## 📦 Archivos Creados

### Eventos (Events)
```
app/Events/Tenant/
├── StudentCreated.php
├── TrainingPlanActivated.php
├── TrainingPlanExpiredWithoutReplacement.php
├── ContactFormSubmitted.php
└── MessageReceivedWhileOffline.php
```

### Listeners
```
app/Listeners/Tenant/
├── SendStudentWelcomeNotification.php
├── NotifyTrainingPlanActivation.php
├── NotifyPlanExpiredWithoutReplacement.php
├── NotifyContactFormSubmission.php
└── NotifyOfflineMessageRecipient.php
```

### Notificaciones (Notifications)
```
app/Notifications/
├── WelcomeStudentNotification.php
├── TrainingPlanActivatedNotification.php
├── StudentWithoutPlanNotification.php
└── NewMessageNotification.php
```

### Providers
```
app/Providers/
└── EventServiceProvider.php ← Registra eventos y listeners
```

---

## 🎯 Eventos Disponibles

| Evento | Cuándo se Dispara | Notificación |
|--------|------------------|--------------|
| **StudentCreated** | Al crear un alumno | Email de bienvenida al alumno |
| **TrainingPlanActivated** | Al activar un plan (manual o automático) | Email al alumno con detalles del plan |
| **TrainingPlanExpiredWithoutReplacement** | Cuando un plan vence y no hay otro pendiente | Alerta al trainer para asignar nuevo plan |
| **ContactFormSubmitted** | Al recibir consulta por web | Notificación al admin del tenant |
| **MessageReceivedWhileOffline** | Mensaje recibido estando offline | Email al destinatario con el mensaje |

---

## 🚀 Próximos Pasos

### 1. Integrar Eventos en el Código

#### a) Crear Estudiante
**Archivo:** `app/Livewire/Tenant/Students/StudentForm.php`

```php
use App\Events\Tenant\StudentCreated;

public function save()
{
    // ... validación y creación ...
    
    $student = Student::create($validated);
    
    // 🔔 Disparar evento
    event(new StudentCreated($student, auth()->id()));
    
    session()->flash('success', 'Alumno creado correctamente');
}
```

#### b) Activar Plan Manualmente
**Archivo:** `app/Services/Tenant/AssignPlanService.php`

```php
use App\Events\Tenant\TrainingPlanActivated;

public function assign(...)
{
    return DB::transaction(function () use (...) {
        // ... código existente ...
        
        $assignment->save();
        
        // 🔔 Disparar evento si se activa inmediatamente
        if ($assignment->is_active) {
            event(new TrainingPlanActivated($assignment, 'manual'));
        }
        
        return $assignment;
    });
}
```

#### c) Activar Plan Automáticamente (Cron)
**Archivo:** `app/Console/Commands/ActivatePendingPlans.php`

```php
use App\Events\Tenant\TrainingPlanActivated;

// Dentro del loop de activación:
$plan->update([
    'status' => PlanAssignmentStatus::ACTIVE,
    'is_active' => true,
]);

// 🔔 Disparar evento
event(new TrainingPlanActivated($plan, 'automatic'));
```

#### d) Plan Vencido sin Reemplazo
**Archivo:** `app/Console/Commands/DeactivateExpiredPlans.php`

```php
use App\Events\Tenant\TrainingPlanExpiredWithoutReplacement;
use App\Enums\PlanAssignmentStatus;

$plan->update([
    'status' => PlanAssignmentStatus::COMPLETED,
    'is_active' => false,
]);

// Verificar si hay plan pendiente
$hasPendingPlan = StudentPlanAssignment::where('student_id', $plan->student_id)
    ->where('status', PlanAssignmentStatus::PENDING)
    ->exists();

// 🔔 Si no hay plan pendiente, disparar evento
if (!$hasPendingPlan) {
    event(new TrainingPlanExpiredWithoutReplacement($plan));
}
```

---

### 2. Configurar Sistema de Colas

Para que las notificaciones se procesen en segundo plano:

#### Configurar .env
```env
QUEUE_CONNECTION=database
```

#### Crear tabla de jobs
```bash
php artisan queue:table
php artisan migrate
```

#### Ejecutar worker en producción
```bash
php artisan queue:work --tries=3 --timeout=90
```

O configurar Supervisor para que ejecute el worker automáticamente.

---

### 3. Configurar Email (si no está configurado)

En `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@fittrack.com
MAIL_FROM_NAME="FitTrack"
```

---

## 🧪 Testing Manual

### Probar evento de estudiante creado

```bash
php artisan tinker
```

```php
use App\Models\Tenant\Student;
use App\Events\Tenant\StudentCreated;

$student = Student::first();
event(new StudentCreated($student, 1));
```

### Verificar logs
```bash
tail -f storage/logs/laravel.log
```

### Ejecutar un job de la cola
```bash
php artisan queue:work --once
```

---

## 📊 Flujo de Notificaciones

```
┌─────────────────┐
│  Usuario crea   │
│   un alumno     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ StudentCreated  │ ← Evento disparado
│     Event       │
└────────┬────────┘
         │
         ▼
┌──────────────────────────┐
│ SendStudentWelcome       │ ← Listener (queued)
│    Notification          │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│    Queue Worker          │
│  procesa en background   │
└────────┬─────────────────┘
         │
         ├─► 📧 Email al estudiante
         └─► 💾 Registro en BD (opcional)
```

---

## 🎨 Canales de Notificación

Cada notificación puede usar múltiples canales:

| Canal | Descripción | Estado |
|-------|-------------|--------|
| **mail** | Envío de emails | ✅ Implementado |
| **database** | Guardar en BD para mostrar en UI | ✅ Implementado |
| **broadcast** | Tiempo real (Websockets) | ⏳ Pendiente |
| **sms** | Mensajes SMS | ⏳ Pendiente |
| **push** | Push notifications móviles | ⏳ Pendiente |

---

## 📝 Ejemplo de Notificación en la UI

Para mostrar las notificaciones en la interfaz (cuando se usa canal `database`):

```php
// En el controlador o componente Livewire
$notifications = auth()->user()->unreadNotifications;
```

```blade
{{-- En la vista --}}
<div class="notifications-dropdown">
    @foreach($notifications as $notification)
        <div class="notification-item">
            <div class="notification-icon">
                <x-heroicon-o-{{ $notification->data['icon'] }} />
            </div>
            <div class="notification-content">
                <h4>{{ $notification->data['title'] }}</h4>
                <p>{{ $notification->data['message'] }}</p>
                <span class="notification-time">
                    {{ $notification->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
    @endforeach
</div>
```

---

## 🔮 Eventos Adicionales Sugeridos

Para futuras implementaciones:

- `PaymentReceived` - Pago registrado
- `PaymentOverdue` - Pago vencido
- `AttendanceMarked` - Asistencia marcada
- `ProgressPhotoUploaded` - Foto de progreso subida
- `WorkoutCompleted` - Workout completado
- `StudentInactive` - Estudiante inactivo por X días
- `SubscriptionExpiring` - Suscripción por vencer

---

## 📚 Documentación Completa

Ver documento detallado: [`documents/EVENTOS_LISTENERS_NOTIFICACIONES.md`](./EVENTOS_LISTENERS_NOTIFICACIONES.md)

---

## ✨ Ventajas de Esta Arquitectura

✅ **Desacoplamiento**: Los eventos separan la lógica de negocio de las notificaciones
✅ **Escalabilidad**: Las notificaciones se procesan en background sin afectar rendimiento
✅ **Mantenibilidad**: Fácil agregar nuevos listeners sin modificar código existente
✅ **Testeable**: Cada componente (evento/listener/notificación) es testeable individualmente
✅ **Flexible**: Múltiples canales de notificación (email, DB, broadcast, SMS, push)
✅ **Resiliente**: Manejo de errores con método `failed()` en listeners
✅ **Auditable**: Logs completos de cada evento y notificación

---

**Estado:** ✅ Estructura completada - Pendiente integración en código existente
