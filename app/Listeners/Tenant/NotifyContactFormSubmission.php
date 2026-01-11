<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\ContactFormSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyContactFormSubmission implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ContactFormSubmitted $event): void
    {
        // TODO: Implementar lógica de notificación
        // - Notificar al administrador del tenant sobre la consulta
        // - Enviar email de confirmación al usuario que envió el formulario
        // - Crear registro en sistema de CRM si existe

        Log::info('Formulario de contacto recibido', [
            'name' => $event->name,
            'email' => $event->email,
            'phone' => $event->phone,
            'source' => $event->source,
            'message_preview' => substr($event->message, 0, 100),
        ]);

        // Ejemplo de estructura para notificación:
        // $tenantAdmins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
        //
        // foreach ($tenantAdmins as $admin) {
        //     $admin->notify(new ContactFormReceivedNotification(
        //         $event->name,
        //         $event->email,
        //         $event->phone,
        //         $event->message
        //     ));
        // }
        //
        // // Enviar confirmación al usuario
        // Mail::to($event->email)->send(new ContactFormConfirmationMail($event->name));
    }

    /**
     * Handle a job failure.
     */
    public function failed(ContactFormSubmitted $event, \Throwable $exception): void
    {
        Log::error('Error al procesar formulario de contacto', [
            'email' => $event->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
