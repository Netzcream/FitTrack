<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentWeightEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentApiController extends Controller
{
    /**
     * GET /api/profile
     *
     * Obtener datos del perfil del estudiante autenticado.
     */
    public function show(Request $request)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatStudentData($student),
        ]);
    }

    /**
     * PATCH /api/profile
     *
     * Actualizar datos generales del perfil del estudiante.
     */
    public function update(Request $request)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'goal' => 'sometimes|nullable|string|max:100',
            'timezone' => 'sometimes|nullable|string|max:50',

            'birth_date' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|string|in:male,female,other',
            'height_cm' => 'sometimes|nullable|numeric|min:50|max:300',
            'weight_kg' => 'sometimes|nullable|numeric|min:20|max:500',

            'language' => 'sometimes|nullable|string|max:10',
            'notifications' => 'sometimes|array',
            'notifications.*' => 'boolean',

            'training_experience' => 'sometimes|nullable|string|max:100',
            'days_per_week' => 'sometimes|nullable|integer|min:1|max:7',

            // Compatibilidad con payload agrupado por contacto
            'contact' => 'sometimes|array',
            'contact.phone' => 'sometimes|nullable|string|max:50',
            'contact.timezone' => 'sometimes|nullable|string|max:50',
            'contact.language' => 'sometimes|nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos invalidos',
                'details' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $payload = array_replace($validated, $this->extractContactPreferencesPayload($request));

        $this->applyProfileUpdates($student, $payload);

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'data' => $this->formatStudentData($student),
        ]);
    }

    /**
     * PATCH /api/profile/preferences
     *
     * Actualizar preferencias de contacto y notificaciones.
     */
    public function updatePreferences(Request $request)
    {
        $student = $this->findStudentFromRequest($request);

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'contact' => 'sometimes|array',
            'contact.phone' => 'sometimes|nullable|string|max:50',
            'contact.timezone' => 'sometimes|nullable|string|max:50',
            'contact.language' => 'sometimes|nullable|string|max:10',
            'phone' => 'sometimes|nullable|string|max:50',
            'timezone' => 'sometimes|nullable|string|max:50',
            'language' => 'sometimes|nullable|string|max:10',
            'notifications' => 'sometimes|array',
            'notifications.*' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos invalidos',
                'details' => $validator->errors(),
            ], 422);
        }

        $payload = $this->extractContactPreferencesPayload($request);

        if ($payload === []) {
            return response()->json([
                'error' => 'Datos invalidos',
                'details' => [
                    'payload' => [
                        'Debes enviar al menos uno: contact, phone, timezone, language, notifications',
                    ],
                ],
            ], 422);
        }

        $this->applyProfileUpdates($student, $payload);

        return response()->json([
            'message' => 'Preferencias de contacto actualizadas correctamente',
            'data' => $this->formatStudentData($student),
        ]);
    }

    private function findStudentFromRequest(Request $request): ?Student
    {
        $email = $request->user()?->email;

        if (!is_string($email) || trim($email) === '') {
            return null;
        }

        return Student::where('email', $email)->first();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function applyProfileUpdates(Student $student, array $payload): void
    {
        foreach (['first_name', 'last_name', 'phone', 'goal'] as $field) {
            if (array_key_exists($field, $payload)) {
                $student->{$field} = $payload[$field];
            }
        }

        $data = is_array($student->data) ? $student->data : [];

        if (array_key_exists('timezone', $payload)) {
            $data['timezone'] = $this->normalizeNullableString($payload['timezone'] ?? null);
        }

        if (array_key_exists('birth_date', $payload)) {
            $data['birth_date'] = $payload['birth_date'] ?? null;
        }

        if (array_key_exists('gender', $payload)) {
            $data['gender'] = $this->normalizeNullableString($payload['gender'] ?? null);
        }

        if (array_key_exists('height_cm', $payload)) {
            $data['height_cm'] = isset($payload['height_cm']) ? (float) $payload['height_cm'] : null;
        }

        if (array_key_exists('weight_kg', $payload)) {
            $weightKg = isset($payload['weight_kg']) ? (float) $payload['weight_kg'] : null;
            $data['weight_kg'] = $weightKg;

            if ($weightKg !== null) {
                $this->createWeightEntryIfNeeded($student, $weightKg);
            }
        }

        if (array_key_exists('language', $payload)) {
            $communicationData = is_array($data['communication_data'] ?? null)
                ? $data['communication_data']
                : [];
            $communicationData['language'] = $this->normalizeNullableString($payload['language'] ?? null);
            $data['communication_data'] = $communicationData;
        }

        if (array_key_exists('notifications', $payload)) {
            $existingNotifications = $this->normalizeNotificationsPayload($data['notifications'] ?? []);
            $incomingNotifications = $this->normalizeNotificationsPayload($payload['notifications']);
            $data['notifications'] = $incomingNotifications === []
                ? []
                : array_replace($existingNotifications, $incomingNotifications);
        }

        if (array_key_exists('training_experience', $payload) || array_key_exists('days_per_week', $payload)) {
            $trainingData = is_array($data['training_data'] ?? null)
                ? $data['training_data']
                : [];

            if (array_key_exists('training_experience', $payload)) {
                $trainingData['experience'] = $this->normalizeNullableString($payload['training_experience'] ?? null);
            }

            if (array_key_exists('days_per_week', $payload)) {
                $trainingData['days_per_week'] = isset($payload['days_per_week'])
                    ? (int) $payload['days_per_week']
                    : null;
            }

            $data['training_data'] = $trainingData;
        }

        $student->data = $data;
        $student->save();
        $student->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function extractContactPreferencesPayload(Request $request): array
    {
        $payload = [];
        $contact = $request->input('contact');

        if (is_array($contact)) {
            foreach (['phone', 'timezone', 'language'] as $field) {
                if (array_key_exists($field, $contact)) {
                    $payload[$field] = $contact[$field];
                }
            }
        }

        foreach (['phone', 'timezone', 'language'] as $field) {
            if ($request->exists($field)) {
                $payload[$field] = $request->input($field);
            }
        }

        if ($request->exists('notifications')) {
            $payload['notifications'] = $request->input('notifications', []);
        }

        return $payload;
    }

    private function createWeightEntryIfNeeded(Student $student, float $weightKg): void
    {
        $latestWeight = $student->weightEntries()
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->value('weight_kg');

        if ($latestWeight !== null && abs((float) $latestWeight - $weightKg) < 0.0001) {
            return;
        }

        StudentWeightEntry::create([
            'student_id' => $student->id,
            'weight_kg' => $weightKg,
            'source' => 'api',
            'recorded_at' => now(),
            'notes' => 'Recorded via profile update',
        ]);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @return array<string, bool>
     */
    private function normalizeNotificationsPayload(mixed $notifications): array
    {
        if (!is_array($notifications)) {
            return [];
        }

        $normalized = [];

        foreach ($notifications as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                continue;
            }

            $normalized[$key] = $this->normalizeBoolean($value);
        }

        return $normalized;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    private function resolveCurrentWeightKg(Student $student): ?float
    {
        $student->loadMissing('latestWeight');

        $latestWeight = $student->latestWeight?->weight_kg;
        if ($latestWeight !== null) {
            return (float) $latestWeight;
        }

        $profileWeight = $student->weight_kg;

        return $profileWeight !== null ? (float) $profileWeight : null;
    }

    private function calculateImc(?float $heightCm, ?float $weightKg): ?float
    {
        if ($heightCm === null || $weightKg === null || $heightCm <= 0) {
            return null;
        }

        $heightMeters = $heightCm / 100;

        return round($weightKg / ($heightMeters ** 2), 1);
    }

    /**
     * Formatear datos del estudiante para la respuesta.
     *
     * @return array<string, mixed>
     */
    private function formatStudentData(Student $student): array
    {
        $avatarMedia = $student->getFirstMedia('avatar');
        $heightCm = $student->height_cm;
        $weightKg = $this->resolveCurrentWeightKg($student);
        $imc = $this->calculateImc($heightCm !== null ? (float) $heightCm : null, $weightKg);

        return [
            'id' => $student->id,
            'uuid' => $student->uuid,
            'email' => $student->email,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'full_name' => $student->full_name,
            'avatar_url' => $avatarMedia?->getUrl(),
            'avatar_thumb_url' => $avatarMedia?->getUrl('thumb'),
            'phone' => $student->phone,
            'goal' => $student->goal,
            'status' => $student->status,
            'timezone' => $student->timezone,
            'current_level' => $student->current_level,

            'birth_date' => $student->birth_date,
            'gender' => $student->gender,
            'height_cm' => $heightCm,
            'weight_kg' => $weightKg,
            'imc' => $imc,

            'language' => $student->language,
            'notifications' => $this->normalizeNotificationsPayload($student->notifications),

            'training_experience' => $student->training_data['experience'] ?? null,
            'days_per_week' => $student->training_data['days_per_week'] ?? null,

            'commercial_plan_id' => $student->commercial_plan_id,
            'billing_frequency' => $student->billing_frequency,
            'account_status' => $student->account_status,
        ];
    }
}
