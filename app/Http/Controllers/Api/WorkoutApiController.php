<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Student;
use App\Models\Tenant\Workout;
use App\Enums\WorkoutStatus;
use App\Services\Api\WorkoutDataFormatter;
use App\Services\WorkoutOrchestrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkoutApiController extends Controller
{
    public function __construct(
        protected WorkoutOrchestrationService $orchestration,
        protected WorkoutDataFormatter $workoutDataFormatter
    ) {}

    /**
     * GET /api/workouts
     *
     * Listar todos los workouts del estudiante (con filtro por status)
     */
    public function index(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Filtrar por status si se proporciona
        $status = $request->query('status'); // pending|in_progress|completed|skipped

        $query = $student->workouts()
            ->with('planAssignment')
            ->orderByDesc('created_at');

        if ($status && in_array($status, ['pending', 'in_progress', 'completed', 'skipped'])) {
            $query->where('status', $status);
        }

        $workouts = $query->get();

        return response()->json([
            'data' => $workouts->map(function ($workout) {
                return $this->workoutDataFormatter->format($workout);
            })
        ]);
    }

    /**
     * GET /api/workouts/{id}
     *
     * Obtener detalles completos de un workout específico
     */
    public function show(Request $request, $id)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Buscar el workout y verificar que pertenezca al estudiante
        $workout = Workout::where('id', $id)
            ->where('student_id', $student->id)
            ->with(['planAssignment'])
            ->first();

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        return response()->json([
            'data' => $this->workoutDataFormatter->format($workout)
        ]);
    }

    /**
     * POST /api/workouts/today
     *
     * Obtener o crear el workout de hoy basado en el plan activo
     */
    public function today(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Resolver plan activo
        $plan = $this->orchestration->resolveActivePlan($student);

        if (!$plan) {
            return response()->json([
                'data' => null,
                'message' => 'No active training plan assigned'
            ]);
        }

        // Obtener o crear workout
        $workout = $this->orchestration->getOrCreateTodayWorkout($student, $plan);

        if (!$workout) {
            return response()->json([
                'error' => 'Failed to create workout'
            ], 500);
        }

        return response()->json([
            'data' => $this->workoutDataFormatter->format($workout)
        ]);
    }

    /**
     * POST /api/workouts/{id}/start
     *
     * Iniciar un workout (cambiar status a in_progress)
     */
    public function start(Request $request, $id)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $workout = Workout::where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        if ($workout->status !== WorkoutStatus::PENDING &&
            $workout->status !== WorkoutStatus::IN_PROGRESS) {
            return response()->json([
                'error' => 'Cannot start a workout with status ' . $workout->status->value
            ], 422);
        }

        $workout->startWorkout();

        return response()->json([
            'message' => 'Workout started',
            'data' => $this->workoutDataFormatter->format($workout)
        ]);
    }

    /**
     * PATCH /api/workouts/{id}
     *
     * Actualizar datos de ejercicios durante la sesión
     */
    public function update(Request $request, $id)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $workout = Workout::where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        // Validar que sea un array de ejercicios
        $validator = Validator::make($request->all(), [
            'exercises' => 'required|array',
            'exercises.*.id' => 'required',
            'exercises.*.name' => 'string',
            'exercises.*.completed' => 'boolean',
            'exercises.*.sets' => 'array',
            'exercises.*.sets.*.reps' => 'integer|min:0',
            'exercises.*.sets.*.weight' => 'numeric|min:0',
            'exercises.*.sets.*.duration_seconds' => 'integer|min:0',
            'exercises.*.sets.*.completed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid exercise data',
                'details' => $validator->errors()
            ], 422);
        }

        // Enriquecer con datos existentes (preservar fields como description, etc)
        $enrichedExercises = collect($request->exercises)->map(function ($updated) use ($workout) {
            $existing = collect($workout->exercises_data)->firstWhere('id', $updated['id']) ?? [];
            return array_merge($existing, $updated);
        })->toArray();

        $workout->updateExercisesData($enrichedExercises);

        return response()->json([
            'message' => 'Exercises updated',
            'data' => $this->workoutDataFormatter->format($workout)
        ]);
    }

    /**
     * POST /api/workouts/{id}/complete
     *
     * Completar un workout con duración, rating, notas y survey
     */
    public function complete(Request $request, $id)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $workout = Workout::where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        // Validar datos
        $validator = Validator::make($request->all(), [
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'rating' => 'sometimes|integer|min:1|max:5',
            'notes' => 'sometimes|string|max:1000',
            'survey' => 'sometimes|array',
            'survey.fatigue' => 'integer|min:1|max:5',
            'survey.rpe' => 'integer|min:6|max:20',
            'survey.pain' => 'integer|min:0|max:10',
            'survey.mood' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid data',
                'details' => $validator->errors()
            ], 422);
        }

        $workout->completeWorkout(
            durationMinutes: $request->duration_minutes,
            rating: $request->rating,
            notes: $request->notes,
            survey: $request->survey ?? []
        );

        return response()->json([
            'message' => 'Workout completed',
            'data' => $this->workoutDataFormatter->format($workout)
        ]);
    }

    /**
     * POST /api/workouts/{id}/skip
     *
     * Saltar un workout
     */
    public function skip(Request $request, $id)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $workout = Workout::where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid data'], 422);
        }

        $workout->skip($request->reason ?? null);

        return response()->json([
            'message' => 'Workout skipped',
            'data' => $this->workoutDataFormatter->format($workout)
        ]);
    }

    /**
     * GET /api/workouts/stats
     *
     * Obtener estadísticas generales de workouts
     */
    public function stats(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $completedCount = $student->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->count();

        $pendingCount = $student->workouts()
            ->where('status', WorkoutStatus::PENDING)
            ->count();

        $skippedCount = $student->workouts()
            ->where('status', WorkoutStatus::SKIPPED)
            ->count();

        $averageDuration = $this->orchestration->getAverageDuration($student);
        $averageRating = $this->orchestration->getAverageRating($student);

        return response()->json([
            'data' => [
                'completed_workouts' => $completedCount,
                'pending_workouts' => $pendingCount,
                'skipped_workouts' => $skippedCount,
                'average_duration_minutes' => $averageDuration,
                'average_rating' => $averageRating,
                'total_duration_minutes' => $student->workouts()
                    ->where('status', WorkoutStatus::COMPLETED)
                    ->sum('duration_minutes'),
            ]
        ]);
    }

}
