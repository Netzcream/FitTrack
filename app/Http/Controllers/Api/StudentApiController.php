<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Student;
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
        // Buscar el estudiante por el email del usuario autenticado
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.'
            ], 404);
        }

        return response()->json([
            'data' => $this->formatStudentData($student)
        ]);
    }

    /**
     * PATCH /api/profile
     *
     * Actualizar datos del perfil del estudiante.
     */
    public function update(Request $request)
    {
        // Buscar el estudiante
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.'
            ], 404);
        }

        // Validar los datos recibidos
        $validator = Validator::make($request->all(), [
            'first_name'          => 'sometimes|string|max:255',
            'last_name'           => 'sometimes|string|max:255',
            'phone'               => 'sometimes|string|max:50',
            'goal'                => 'sometimes|string|max:100',
            'timezone'            => 'sometimes|string|max:50',

            // Datos personales
            'birth_date'          => 'sometimes|date',
            'gender'              => 'sometimes|string|in:male,female,other',
            'height_cm'           => 'sometimes|numeric|min:50|max:300',
            'weight_kg'           => 'sometimes|numeric|min:20|max:500',

            // Datos de comunicación
            'language'            => 'sometimes|string|max:10',
            'notifications'       => 'sometimes|array',

            // Datos de entrenamiento
            'training_experience' => 'sometimes|string|max:100',
            'days_per_week'       => 'sometimes|integer|min:1|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'   => 'Datos inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        // Actualizar campos directos
        $directFields = ['first_name', 'last_name', 'phone', 'goal', 'timezone'];
        foreach ($directFields as $field) {
            if ($request->has($field)) {
                $student->{$field} = $request->{$field};
            }
        }

        // Actualizar datos personales usando los mutadores
        $personalFields = ['birth_date', 'gender', 'height_cm', 'weight_kg'];
        foreach ($personalFields as $field) {
            if ($request->has($field)) {
                $student->{$field} = $request->{$field};
            }
        }

        // Actualizar datos de comunicación
        if ($request->has('language')) {
            $student->language = $request->language;
        }
        if ($request->has('notifications')) {
            $student->notifications = $request->notifications;
        }

        // Actualizar datos de entrenamiento
        if ($request->has('training_experience') || $request->has('days_per_week')) {
            $trainingData = $student->training_data ?? [];

            if ($request->has('training_experience')) {
                $trainingData['experience'] = $request->training_experience;
            }
            if ($request->has('days_per_week')) {
                $trainingData['days_per_week'] = $request->days_per_week;
            }

            $student->training_data = $trainingData;
        }

        $student->save();

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'data'    => $this->formatStudentData($student)
        ]);
    }

    /**
     * Formatear datos del estudiante para la respuesta
     */
    private function formatStudentData(Student $student): array
    {
        $avatarMedia = $student->getFirstMedia('avatar');

        return [
            'id'                  => $student->id,
            'uuid'                => $student->uuid,
            'email'               => $student->email,
            'first_name'          => $student->first_name,
            'last_name'           => $student->last_name,
            'full_name'           => $student->full_name,
            'avatar_url'          => $avatarMedia?->getUrl(),
            'avatar_thumb_url'    => $avatarMedia?->getUrl('thumb'),
            'phone'               => $student->phone,
            'goal'                => $student->goal,
            'status'              => $student->status,
            'timezone'            => $student->timezone,
            'current_level'       => $student->current_level,

            // Datos personales
            'birth_date'          => $student->birth_date,
            'gender'              => $student->gender,
            'height_cm'           => $student->height_cm,
            'weight_kg'           => $student->weight_kg,
            'imc'                 => $student->imc,

            // Datos de comunicación
            'language'            => $student->language,
            'notifications'       => $student->notifications,

            // Datos de entrenamiento
            'training_experience' => $student->training_data['experience'] ?? null,
            'days_per_week'       => $student->training_data['days_per_week'] ?? null,

            // Datos adicionales
            'commercial_plan_id'  => $student->commercial_plan_id,
            'billing_frequency'   => $student->billing_frequency,
            'account_status'      => $student->account_status,
        ];
    }
}
