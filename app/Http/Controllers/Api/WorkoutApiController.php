<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Workout;
use App\Models\Tenant\WorkoutExercise;
use App\Models\Tenant\Student;
use App\Models\Tenant\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WorkoutApiController extends Controller
{
    /**
     * POST /api/workouts
     *
     * Registrar una nueva sesión de entrenamiento.
     */
    public function store(Request $request)
    {
        // Buscar el estudiante
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.'
            ], 404);
        }

        // Validar datos
        $validator = Validator::make($request->all(), [
            'training_plan_id'  => 'nullable|exists:training_plans,id',
            'date'              => 'required|date',
            'duration_minutes'  => 'nullable|integer|min:1',
            'status'            => 'nullable|string|in:completed,in_progress,skipped',
            'notes'             => 'nullable|string',
            'rating'            => 'nullable|integer|min:1|max:5',
            'exercises'         => 'required|array|min:1',
            'exercises.*.exercise_id'      => 'required|exists:exercises,id',
            'exercises.*.sets_completed'   => 'nullable|integer|min:0',
            'exercises.*.reps_per_set'     => 'nullable|array',
            'exercises.*.weight_used_kg'   => 'nullable|numeric|min:0',
            'exercises.*.duration_seconds' => 'nullable|integer|min:0',
            'exercises.*.rest_time_seconds'=> 'nullable|integer|min:0',
            'exercises.*.notes'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'   => 'Datos inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Crear el workout
            $workout = Workout::create([
                'student_id'       => $student->id,
                'training_plan_id' => $request->training_plan_id,
                'date'             => $request->date,
                'duration_minutes' => $request->duration_minutes,
                'status'           => $request->status ?? 'completed',
                'notes'            => $request->notes,
                'rating'           => $request->rating,
            ]);

            // Crear los ejercicios del workout
            foreach ($request->exercises as $exerciseData) {
                WorkoutExercise::create([
                    'workout_id'         => $workout->id,
                    'exercise_id'        => $exerciseData['exercise_id'],
                    'sets_completed'     => $exerciseData['sets_completed'] ?? null,
                    'reps_per_set'       => $exerciseData['reps_per_set'] ?? null,
                    'weight_used_kg'     => $exerciseData['weight_used_kg'] ?? null,
                    'duration_seconds'   => $exerciseData['duration_seconds'] ?? null,
                    'rest_time_seconds'  => $exerciseData['rest_time_seconds'] ?? null,
                    'notes'              => $exerciseData['notes'] ?? null,
                    'completed_at'       => now(),
                ]);
            }

            DB::commit();

            // Cargar relaciones para la respuesta
            $workout->load(['exercises.exercise', 'trainingPlan']);

            return response()->json([
                'message' => 'Sesión de entrenamiento registrada correctamente',
                'data'    => $this->formatWorkoutData($workout)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Error al registrar la sesión de entrenamiento',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/workouts
     *
     * Listar todas las sesiones de entrenamiento del estudiante.
     */
    public function index(Request $request)
    {
        // Buscar el estudiante
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json([
                'error' => 'Perfil de estudiante no encontrado.'
            ], 404);
        }

        // Filtros opcionales
        $query = Workout::where('student_id', $student->id)
            ->with(['exercises.exercise', 'trainingPlan']);

        // Filtrar por fecha
        if ($request->has('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        // Filtrar por plan
        if ($request->has('training_plan_id')) {
            $query->where('training_plan_id', $request->training_plan_id);
        }

        // Ordenar por fecha descendente
        $workouts = $query->orderBy('date', 'desc')
            ->limit($request->limit ?? 50)
            ->get();

        return response()->json([
            'data' => $workouts->map(function ($workout) {
                return $this->formatWorkoutData($workout);
            })
        ]);
    }

    /**
     * GET /api/workouts/{id}
     *
     * Obtener detalles de una sesión de entrenamiento específica.
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

        // Buscar el workout
        $workout = Workout::where('id', $id)
            ->where('student_id', $student->id)
            ->with(['exercises.exercise', 'trainingPlan'])
            ->first();

        if (!$workout) {
            return response()->json([
                'error' => 'Sesión de entrenamiento no encontrada.'
            ], 404);
        }

        return response()->json([
            'data' => $this->formatWorkoutData($workout)
        ]);
    }

    /**
     * Formatear datos del workout para la respuesta
     */
    private function formatWorkoutData(Workout $workout): array
    {
        return [
            'id'                => $workout->id,
            'uuid'              => $workout->uuid,
            'date'              => $workout->date->format('Y-m-d'),
            'duration_minutes'  => $workout->duration_minutes,
            'status'            => $workout->status,
            'notes'             => $workout->notes,
            'rating'            => $workout->rating,
            'exercises_count'   => $workout->exercises->count(),

            // Plan asociado
            'training_plan'     => $workout->trainingPlan ? [
                'id'   => $workout->trainingPlan->id,
                'name' => $workout->trainingPlan->name,
                'goal' => $workout->trainingPlan->goal,
            ] : null,

            // Ejercicios detallados
            'exercises' => $workout->exercises->map(function ($workoutExercise) {
                return [
                    'id'                 => $workoutExercise->id,
                    'exercise_id'        => $workoutExercise->exercise_id,
                    'exercise_name'      => $workoutExercise->exercise->name ?? 'N/A',
                    'sets_completed'     => $workoutExercise->sets_completed,
                    'reps_per_set'       => $workoutExercise->reps_per_set,
                    'weight_used_kg'     => $workoutExercise->weight_used_kg,
                    'duration_seconds'   => $workoutExercise->duration_seconds,
                    'rest_time_seconds'  => $workoutExercise->rest_time_seconds,
                    'notes'              => $workoutExercise->notes,
                    'completed_at'       => $workoutExercise->completed_at?->format('Y-m-d H:i:s'),
                ];
            }),

            'created_at' => $workout->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $workout->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
