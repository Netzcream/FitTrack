<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Student;
use Illuminate\Http\Request;

class TrainingPlanApiController extends Controller
{
    /**
     * GET /api/plans
     *
     * Listar todos los planes de entrenamiento asignados al estudiante.
     */
    public function index(Request $request)
    {
        // Buscar el estudiante por el email del usuario autenticado
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.'
            ], 404);
        }

        // Obtener planes asignados al estudiante
        $plans = TrainingPlan::where('student_id', $student->id)
            ->with('exercises')
            ->orderBy('assigned_from', 'desc')
            ->get();

        return response()->json([
            'data' => $plans->map(function ($plan) {
                return [
                    'id'              => $plan->id,
                    'uuid'            => $plan->uuid,
                    'name'            => $plan->name,
                    'description'     => $plan->description,
                    'goal'            => $plan->goal,
                    'duration'        => $plan->duration,
                    'is_active'       => $plan->is_active,
                    'assigned_from'   => $plan->assigned_from?->format('Y-m-d'),
                    'assigned_until'  => $plan->assigned_until?->format('Y-m-d'),
                    'exercises_count' => $plan->exercises->count(),
                    'created_at'      => $plan->created_at?->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    /**
     * GET /api/plans/{id}
     *
     * Obtener detalles completos de un plan de entrenamiento específico,
     * incluyendo todos sus ejercicios con información detallada.
     */
    public function show(Request $request, $id)
    {
        // Buscar el estudiante
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.'
            ], 404);
        }

        // Buscar el plan que pertenezca al estudiante
        $plan = TrainingPlan::where('id', $id)
            ->where('student_id', $student->id)
            ->with(['exercises'])
            ->first();

        if (!$plan) {
            return response()->json([
                'error' => 'Plan de entrenamiento no encontrado o no pertenece al estudiante.'
            ], 404);
        }

        // Formatear ejercicios con detalles del pivot
        $exercises = $plan->exercises->map(function ($exercise) {
            $detail = $exercise->pivot->detail ?? [];

            return [
                'id'          => $exercise->id,
                'uuid'        => $exercise->uuid,
                'name'        => $exercise->name,
                'description' => $exercise->description,
                'category'    => $exercise->category,
                'muscle_group'=> $exercise->muscle_group,
                'difficulty'  => $exercise->difficulty,

                // Datos del pivot (plan_exercise)
                'day'         => $exercise->pivot->day,
                'sets'        => $detail['sets'] ?? null,
                'reps'        => $detail['reps'] ?? null,
                'weight'      => $detail['weight'] ?? null,
                'duration'    => $detail['duration'] ?? null,
                'rest_time'   => $detail['rest_time'] ?? null,
                'tempo'       => $detail['tempo'] ?? null,
                'notes'       => $exercise->pivot->notes,

                // Media (videos, imágenes)
                'video_url'   => $exercise->getFirstMediaUrl('videos'),
                'image_url'   => $exercise->getFirstMediaUrl('images'),
            ];
        });

        return response()->json([
            'data' => [
                'id'             => $plan->id,
                'uuid'           => $plan->uuid,
                'name'           => $plan->name,
                'description'    => $plan->description,
                'goal'           => $plan->goal,
                'duration'       => $plan->duration,
                'is_active'      => $plan->is_active,
                'assigned_from'  => $plan->assigned_from?->format('Y-m-d'),
                'assigned_until' => $plan->assigned_until?->format('Y-m-d'),
                'meta'           => $plan->meta,
                'exercises'      => $exercises,
                'created_at'     => $plan->created_at?->format('Y-m-d H:i:s'),
                'updated_at'     => $plan->updated_at?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * GET /api/plans/current
     *
     * Obtener el plan de entrenamiento activo actual del estudiante.
     */
    public function current(Request $request)
    {
        // Buscar el estudiante
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.'
            ], 404);
        }

        // Buscar el plan activo actual (dentro del rango de fechas)
        $currentPlan = TrainingPlan::where('student_id', $student->id)
            ->where('is_active', true)
            ->whereDate('assigned_from', '<=', now())
            ->whereDate('assigned_until', '>=', now())
            ->with(['exercises'])
            ->first();

        if (!$currentPlan) {
            return response()->json([
                'data' => null,
                'message' => 'No tienes un plan de entrenamiento activo actualmente.'
            ]);
        }

        // Formatear ejercicios
        $exercises = $currentPlan->exercises->map(function ($exercise) {
            $detail = $exercise->pivot->detail ?? [];

            return [
                'id'          => $exercise->id,
                'name'        => $exercise->name,
                'day'         => $exercise->pivot->day,
                'sets'        => $detail['sets'] ?? null,
                'reps'        => $detail['reps'] ?? null,
                'weight'      => $detail['weight'] ?? null,
                'video_url'   => $exercise->getFirstMediaUrl('videos'),
            ];
        });

        return response()->json([
            'data' => [
                'id'             => $currentPlan->id,
                'name'           => $currentPlan->name,
                'goal'           => $currentPlan->goal,
                'assigned_from'  => $currentPlan->assigned_from?->format('Y-m-d'),
                'assigned_until' => $currentPlan->assigned_until?->format('Y-m-d'),
                'exercises'      => $exercises,
            ]
        ]);
    }
}
