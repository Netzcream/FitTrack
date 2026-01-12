<?php

namespace App\Services;

use App\Models\Tenant\Student;
use Carbon\Carbon;

/**
 * Gestiona onboarding de notificaciones y preferencias de recordatorios
 * del estudiante para entrenamientos.
 */
class WorkoutNotificationPreferencesService
{
    /**
     * Estructura esperada en student.data['notifications']:
     * [
     *     'workout_reminders_enabled' => true,
     *     'preferred_days' => ['monday', 'tuesday', ...],
     *     'preferred_times' => ['08:00', '18:00', ...],
     *     'channels' => ['push', 'email', 'sms'],
     *     'timezone' => 'America/New_York',
     *     'reminder_minutes_before' => 30, // Recordar X minutos antes
     *     'rest_between_reminders_hours' => 24, // No recordar más de una vez cada 24h
     * ]
     */

    /**
     * Obtener preferencias de notificaciones del estudiante
     */
    public function getPreferences(Student $student): array
    {
        $notifications = $student->data['notifications'] ?? [];

        return [
            'enabled' => $notifications['workout_reminders_enabled'] ?? true,
            'preferred_days' => $notifications['preferred_days'] ?? [],
            'preferred_times' => $notifications['preferred_times'] ?? [],
            'channels' => $notifications['channels'] ?? ['push'],
            'timezone' => $student->data['timezone'] ?? config('app.timezone'),
            'reminder_minutes_before' => $notifications['reminder_minutes_before'] ?? 30,
            'rest_between_reminders_hours' => $notifications['rest_between_reminders_hours'] ?? 24,
        ];
    }

    /**
     * Actualizar preferencias de notificaciones
     */
    public function updatePreferences(Student $student, array $preferences): Student
    {
        $student->data = array_merge($student->data ?? [], [
            'notifications' => [
                'workout_reminders_enabled' => $preferences['enabled'] ?? true,
                'preferred_days' => $preferences['preferred_days'] ?? [],
                'preferred_times' => $preferences['preferred_times'] ?? [],
                'channels' => $preferences['channels'] ?? ['push'],
                'reminder_minutes_before' => $preferences['reminder_minutes_before'] ?? 30,
                'rest_between_reminders_hours' => $preferences['rest_between_reminders_hours'] ?? 24,
            ],
        ]);

        $student->save();

        return $student;
    }

    /**
     * Verificar si hoy es un día preferido de entrenamiento
     */
    public function isPreferredDay(Student $student, ?Carbon $date = null): bool
    {
        $date = $date ?? now();
        $preferences = $this->getPreferences($student);

        if (empty($preferences['preferred_days'])) {
            return true; // Si no hay preferencia, cualquier día es válido
        }

        $dayName = strtolower($date->dayName);
        return in_array($dayName, array_map('strtolower', $preferences['preferred_days']));
    }

    /**
     * Verificar si la hora actual es dentro de una ventana preferida
     */
    public function isPreferredTime(Student $student, ?Carbon $time = null): bool
    {
        $time = $time ?? now();
        $preferences = $this->getPreferences($student);

        if (empty($preferences['preferred_times'])) {
            return true; // Si no hay preferencia, cualquier hora es válida
        }

        $currentTime = $time->format('H:i');
        return in_array($currentTime, $preferences['preferred_times']);
    }

    /**
     * Convertir tiempo a zona horaria del estudiante
     */
    public function convertToStudentTimezone(Student $student, Carbon $dateTime): Carbon
    {
        $timezone = $this->getPreferences($student)['timezone'];
        return $dateTime->setTimezone($timezone);
    }

    /**
     * Obtener próxima ventana válida para recordatorio
     */
    public function getNextReminderWindow(Student $student): ?Carbon
    {
        $preferences = $this->getPreferences($student);
        $timezone = $preferences['timezone'];

        $now = now()->setTimezone($timezone);

        // Si no hay preferencias, usar mañana a las 8 AM
        if (empty($preferences['preferred_days']) && empty($preferences['preferred_times'])) {
            return now()
                ->setTimezone($timezone)
                ->addDay()
                ->setHour(8)
                ->setMinute(0)
                ->setSecond(0);
        }

        $preferredDays = array_map('strtolower', $preferences['preferred_days'] ?? []);
        $preferredTimes = $preferences['preferred_times'] ?? ['08:00'];

        // Iterar desde hoy hacia adelante para encontrar la próxima ventana
        $candidate = $now->copy();

        for ($i = 0; $i < 30; $i++) { // Buscar hasta 30 días adelante
            $dayName = strtolower($candidate->dayName);

            // Verificar si este día está en las preferencias
            if (empty($preferredDays) || in_array($dayName, $preferredDays)) {
                // Probar cada hora preferida
                foreach ($preferredTimes as $time) {
                    [$hour, $minute] = explode(':', $time);
                    $candidateTime = $candidate->copy()
                        ->setHour((int) $hour)
                        ->setMinute((int) $minute)
                        ->setSecond(0);

                    // Si es en el futuro, usarlo
                    if ($candidateTime->isFuture()) {
                        return $candidateTime;
                    }
                }
            }

            $candidate->addDay()->setHour(0)->setMinute(0)->setSecond(0);
        }

        // Fallback: mañana a las 8 AM
        return $now->addDay()->setHour(8)->setMinute(0)->setSecond(0);
    }

    /**
     * Verificar si se debe respetar el reposo entre recordatorios
     */
    public function shouldRespectReminderRest(Student $student, ?Carbon $lastReminder = null): bool
    {
        if (!$lastReminder) {
            return false;
        }

        $preferences = $this->getPreferences($student);
        $restHours = $preferences['rest_between_reminders_hours'];

        return now()->diffInHours($lastReminder) < $restHours;
    }

    /**
     * Verificar si el estudiante debe recibir recordatorio ahora
     * (combina: preferencias, reposo, plan activo)
     */
    public function shouldSendReminderNow(Student $student, ?Carbon $lastReminder = null): bool
    {
        $preferences = $this->getPreferences($student);

        // No enviar si está deshabilitado
        if (!$preferences['enabled']) {
            return false;
        }

        // No enviar si estamos dentro del período de reposo
        if ($this->shouldRespectReminderRest($student, $lastReminder)) {
            return false;
        }

        // No enviar si no es un día preferido
        if (!$this->isPreferredDay($student)) {
            return false;
        }

        // No enviar si no es una hora preferida (con margen de remider_minutes_before)
        $reminderMinutes = $preferences['reminder_minutes_before'];
        $windowStart = now()->subMinutes($reminderMinutes);
        $windowEnd = now()->addMinutes(5);

        $hasMatchingTime = false;
        foreach ($preferences['preferred_times'] as $time) {
            [$hour, $minute] = explode(':', $time);
            $scheduleTime = now()
                ->setHour((int) $hour)
                ->setMinute((int) $minute);

            if ($scheduleTime->isBetween($windowStart, $windowEnd)) {
                $hasMatchingTime = true;
                break;
            }
        }

        return $hasMatchingTime;
    }

    /**
     * Obtener canales activos para notificaciones
     */
    public function getActiveChannels(Student $student): array
    {
        $preferences = $this->getPreferences($student);
        return $preferences['channels'] ?? ['push'];
    }

    /**
     * Verificar si un canal está activo
     */
    public function isChannelActive(Student $student, string $channel): bool
    {
        return in_array($channel, $this->getActiveChannels($student));
    }
}
