# Sistema de Eventos y Listeners para Notificaciones

## 📋 Descripción General

Este documento describe la arquitectura de eventos y listeners implementada en FitTrack para manejar notificaciones y acciones reactivas en el sistema.

## 🎯 Eventos Implementados

### 1. **StudentCreated** - Alumno creado
**Archivo:** `app/Events/Tenant/StudentCreated.php`

**Cuándo se dispara:**
- Al crear un nuevo estudiante en el sistema

**Datos que contiene:**
- `$student`: Instancia del modelo Student
- `$createdBy`: ID del usuario que lo creó (opcional)

**Listener asociado:**
- `SendStudentWelcomeNotification`: Envía notificación de bienvenida al estudiante y notifica al trainer

---

### 2. **TrainingPlanActivated** - Plan de entrenamiento activado
**Archivo:** `app/Events/Tenant/TrainingPlanActivated.php`

**Cuándo se dispara:**
- Cuando se asigna manualmente un plan a un estudiante
- Cuando el cron activa automáticamente un plan pendiente

**Datos que contiene:**
- `$assignment`: Instancia de StudentPlanAssignment
- `$activationType`: 'manual' o 'automatic'

**Listener asociado:**
- `NotifyTrainingPlanActivation`: Notifica al estudiante y al trainer sobre la activación

---

### 3. **TrainingPlanExpiredWithoutReplacement** - Plan vencido sin reemplazo
**Archivo:** `app/Events/Tenant/TrainingPlanExpiredWithoutReplacement.php`

**Cuándo se dispara:**
- Cuando un plan se vence y no hay un plan pendiente para auto-asignarse

**Datos que contiene:**
- `$expiredAssignment`: Instancia del plan que venció

**Listener asociado:**
- `NotifyPlanExpiredWithoutReplacement`: Alerta al trainer que el estudiante quedó sin plan activo

---

### 4. **ContactFormSubmitted** - Formulario de contacto enviado
**Archivo:** `app/Events/Tenant/ContactFormSubmitted.php`

**Cuándo se dispara:**
- Cuando alguien envía el formulario de contacto desde la web

**Datos que contiene:**
- `$name`: Nombre del contacto
- `$email`: Email del contacto
- `$phone`: Teléfono del contacto
- `$message`: Mensaje enviado
- `$source`: Origen de la consulta ('web', 'app', etc.)

**Listener asociado:**
- `NotifyContactFormSubmission`: Notifica al administrador del tenant sobre la consulta recibida

---

### 5. **MessageReceivedWhileOffline** - Mensaje recibido estando offline
**Archivo:** `app/Events/Tenant/MessageReceivedWhileOffline.php`

**Cuándo se dispara:**
- Cuando un mensaje es enviado a un usuario que no está en línea

**Datos que contiene:**
- `$message`: Instancia del mensaje
- `$recipientType`: Tipo de destinatario ('student' o 'tenant')
- `$recipientId`: ID del destinatario

**Listener asociado:**
- `NotifyOfflineMessageRecipient`: Envía notificación email/push al destinatario offline

---

## 🔗 Registro de Eventos

Los eventos están registrados en `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    // Tenant Events - Students
    \App\Events\Tenant\StudentCreated::class => [
        \App\Listeners\Tenant\SendStudentWelcomeNotification::class,
    ],

    // Tenant Events - Training Plans
    \App\Events\Tenant\TrainingPlanActivated::class => [
        \App\Listeners\Tenant\NotifyTrainingPlanActivation::class,
    ],
    \App\Events\Tenant\TrainingPlanExpiredWithoutReplacement::class => [
        \App\Listeners\Tenant\NotifyPlanExpiredWithoutReplacement::class,
    ],

    // Tenant Events - Communication
    \App\Events\Tenant\MessageReceivedWhileOffline::class => [
        \App\Listeners\Tenant\NotifyOfflineMessageRecipient::class,
    ],
    \App\Events\Tenant\ContactFormSubmitted::class => [
        \App\Listeners\Tenant\NotifyContactFormSubmission::class,
    ],
];
```

## 📍 Puntos de Integración

### 1. Disparar evento al crear estudiante

**Archivo a modificar:** `app/Livewire/Tenant/Students/StudentForm.php` o similar

```php
use App\Events\Tenant\StudentCreated;

public function save()
{
    $student = Student::create($validated);
    
    // Disparar evento
    event(new StudentCreated($student, auth()->id()));
    
    session()->flash('success', 'Alumno creado correctamente');
}
```

---

### 2. Disparar evento al activar plan manualmente

**Archivo a modificar:** `app/Services/Tenant/AssignPlanService.php`

```php
use App\Events\Tenant\TrainingPlanActivated;

public function assign(TrainingPlan $template, Student $student, ...)
{
    return DB::transaction(function () use (...) {
        // ... código existente ...
        
        $assignment->save();
        
        // Disparar evento solo si se activa inmediatamente
        if ($assignment->is_active) {
            event(new TrainingPlanActivated($assignment, 'manual'));
        }
        
        return $assignment;
    });
}
```

---

### 3. Disparar evento al activar plan automáticamente (cron)

**Archivo a modificar:** `app/Console/Commands/ActivatePendingPlans.php`

```php
use App\Events\Tenant\TrainingPlanActivated;

public function handle(): int
{
    // ... código existente ...
    
    $plan->update([
        'status' => PlanAssignmentStatus::ACTIVE,
        'is_active' => true,
    ]);
    
    // Disparar evento
    event(new TrainingPlanActivated($plan, 'automatic'));
    
    // ... resto del código ...
}
```

---

### 4. Disparar evento al vencer plan sin reemplazo

**Archivo a modificar:** `app/Console/Commands/DeactivateExpiredPlans.php`

```php
use App\Events\Tenant\TrainingPlanExpiredWithoutReplacement;

public function handle(): int
{
    // ... código existente ...
    
    $plan->update([
        'status' => PlanAssignmentStatus::COMPLETED,
        'is_active' => false,
    ]);
    
    // Verificar si hay plan pendiente
    $hasPendingPlan = StudentPlanAssignment::where('student_id', $plan->student_id)
        ->where('status', PlanAssignmentStatus::PENDING)
        ->exists();
    
    // Si no hay plan pendiente, disparar evento
    if (!$hasPendingPlan) {
        event(new TrainingPlanExpiredWithoutReplacement($plan));
    }
    
    // ... resto del código ...
}
```

---

### 5. Disparar evento al recibir formulario de contacto

**Archivo a crear/modificar:** `app/Http/Controllers/ContactController.php` o similar

```php
use App\Events\Tenant\ContactFormSubmitted;

public function submit(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'required|string|max:20',
        'message' => 'required|string|max:1000',
    ]);
    
    // Disparar evento
    event(new ContactFormSubmitted(
        $validated['name'],
        $validated['email'],
        $validated['phone'],
        $validated['message'],
        'web'
    ));
    
    return response()->json(['success' => true]);
}
```

---

### 6. Disparar evento al recibir mensaje estando offline

**Archivo a modificar:** `app/Listeners/Tenant/NotifyMessageRecipients.php`

```php
use App\Events\Tenant\MessageReceivedWhileOffline;

public function handle(MessageSent $event): void
{
    $message = $event->message;
    $conversation = $message->conversation;
    
    // ... código existente para obtener recipients ...
    
    foreach ($recipients as $recipient) {
        // Verificar si el destinatario está online
        $isOnline = $this->checkIfUserIsOnline($recipient);
        
        if (!$isOnline) {
            // Disparar evento de mensaje offline
            event(new MessageReceivedWhileOffline(
                $message,
                $recipient->participant_type->value,
                $recipient->participant_id
            ));
        } else {
            // Enviar notificación en tiempo real (broadcast, etc.)
            $this->sendRealtimeNotification($recipient, $message);
        }
    }
}

private function checkIfUserIsOnline($recipient): bool
{
    // TODO: Implementar lógica para verificar si usuario está online
    // Puede ser mediante sesiones, websockets, cache, etc.
    return false;
}
```

---

## 🚀 Implementación de Notificaciones

Los listeners actualmente tienen estructura básica con logs. Para implementar notificaciones reales:

### Paso 1: Crear clases de Notificación

```bash
php artisan make:notification WelcomeStudentNotification
php artisan make:notification TrainingPlanActivatedNotification
php artisan make:notification StudentWithoutPlanNotification
php artisan make:notification ContactFormReceivedNotification
php artisan make:notification NewMessageNotification
```

### Paso 2: Implementar canales de notificación

Cada notificación puede usar múltiples canales:
- **mail**: Email
- **database**: Notificaciones en base de datos (para mostrar en UI)
- **broadcast**: Notificaciones en tiempo real (websockets)
- **sms**: Mensajes SMS (via Twilio, etc.)
- **push**: Push notifications móviles

Ejemplo de notificación:

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeStudentNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido a FitTrack')
            ->greeting('¡Hola ' . $notifiable->first_name . '!')
            ->line('Tu cuenta ha sido creada exitosamente.')
            ->line('Ahora puedes acceder a tu panel de estudiante.')
            ->action('Acceder', url('/login'))
            ->line('¡Gracias por unirte!');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Tu cuenta ha sido creada exitosamente',
            'type' => 'welcome',
        ];
    }
}
```

### Paso 3: Configurar Queue para listeners

Los listeners ya implementan `ShouldQueue`, lo que significa que se ejecutarán en segundo plano si tienes configurado el sistema de colas.

Configurar en `.env`:

```env
QUEUE_CONNECTION=database
```

Crear tabla de jobs:

```bash
php artisan queue:table
php artisan migrate
```

Ejecutar worker:

```bash
php artisan queue:work
```

---

## 📊 Diagrama de Flujo

```
┌──────────────────┐
│  Acción Usuario  │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Dispara Evento  │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Event Listener  │
│  (ShouldQueue)   │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Cola (Queue)    │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Procesamiento   │
│  en Background   │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Notificación    │
│  (Email/DB/etc)  │
└──────────────────┘
```

---

## ✅ Checklist de Implementación

### Fase 1: Estructura base (✅ COMPLETADO)
- [x] Crear eventos
- [x] Crear listeners
- [x] Registrar en EventServiceProvider
- [x] Documentación

### Fase 2: Integración de eventos (TODO)
- [ ] Integrar `StudentCreated` en formulario de creación
- [ ] Integrar `TrainingPlanActivated` en AssignPlanService
- [ ] Integrar `TrainingPlanActivated` en comando ActivatePendingPlans
- [ ] Integrar `TrainingPlanExpiredWithoutReplacement` en comando DeactivateExpiredPlans
- [ ] Integrar `ContactFormSubmitted` en controlador de contacto
- [ ] Integrar `MessageReceivedWhileOffline` en listener de mensajes

### Fase 3: Notificaciones (TODO)
- [ ] Crear clases de Notification
- [ ] Configurar canales (mail, database, etc.)
- [ ] Crear templates de email
- [ ] Configurar sistema de colas
- [ ] Testing de notificaciones

### Fase 4: UI para notificaciones (TODO)
- [ ] Crear tabla de notificaciones en UI
- [ ] Implementar badge de contador
- [ ] Implementar marcado como leído
- [ ] Implementar filtros y búsqueda

---

## 🧪 Testing

```bash
# Probar eventos manualmente
php artisan tinker

# Crear estudiante y disparar evento
$student = App\Models\Tenant\Student::first();
event(new App\Events\Tenant\StudentCreated($student, auth()->id()));

# Verificar logs
tail -f storage/logs/laravel.log

# Verificar cola de jobs
php artisan queue:work --once
```

---

## 📝 Notas Adicionales

- Los listeners implementan `ShouldQueue` para procesamiento asíncrono
- Los listeners tienen método `failed()` para manejo de errores
- Todos los eventos logean información para debugging
- Los eventos están en contexto tenant (usan conexión de tenant)

---

## 🔮 Eventos Adicionales Sugeridos

Otros eventos que podrías implementar en el futuro:

1. **StudentUpdated** - Cuando se actualiza perfil de estudiante
2. **TrainingPlanModified** - Cuando se modifica un plan activo
3. **PaymentReceived** - Cuando se registra un pago
4. **PaymentOverdue** - Cuando un pago está vencido
5. **AttendanceMarked** - Cuando se marca asistencia
6. **ProgressPhotoUploaded** - Cuando estudiante sube foto de progreso
7. **MeasurementRecorded** - Cuando se registran medidas corporales
8. **WorkoutCompleted** - Cuando estudiante completa un workout
9. **StudentInactive** - Cuando estudiante no ha tenido actividad en X días
10. **SubscriptionExpiring** - Cuando la suscripción del tenant está por vencer

---

## 📚 Referencias

- [Laravel Events Documentation](https://laravel.com/docs/events)
- [Laravel Notifications Documentation](https://laravel.com/docs/notifications)
- [Laravel Queues Documentation](https://laravel.com/docs/queues)
