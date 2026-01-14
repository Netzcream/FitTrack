<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Student;
use App\Services\Tenant\ProgressDashboardService;
use App\Services\Tenant\StudentHomeDashboardService;
use App\Services\Tenant\StudentPaymentsDashboardService;
use App\Services\WorkoutOrchestrationService;
use Illuminate\Http\Request;

class ProgressApiController extends Controller
{
    public function __construct(
        protected WorkoutOrchestrationService $orchestration
    ) {}

    /**
     * GET /api/home
     *
     * Obtener TODOS los datos del home/dashboard del estudiante en una sola consulta
     * Incluye: datos estudiante, plan activo, workout de hoy, historial de planes
     */
    public function home(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $service = new StudentHomeDashboardService();
        $data = $service->getStudentHomeData($student);

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * GET /api/progress/dashboard
     *
     * Obtener TODOS los datos del dashboard de progreso en una sola consulta
     * Incluye: métricas, entrenamientos, peso, progreso mensual, recientes
     */
    public function dashboard(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $data = ProgressDashboardService::getDashboardData($student);

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * GET /api/payments
     *
     * Obtener TODOS los datos de pagos en una sola consulta
     * Incluye: invoice pendiente, métodos de pago, historial, estadísticas
     */
    public function payments(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $service = new StudentPaymentsDashboardService();
        $data = $service->getPaymentsDashboardData($student);

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * GET /api/progress
     *
     * Obtener resumen completo de progreso del plan actual
     */
    public function index(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $summary = $this->orchestration->getProgressSummary($student);

        return response()->json([
            'data' => $summary
        ]);
    }

    /**
     * GET /api/progress/recent
     *
     * Obtener últimos workouts completados (historial)
     */
    public function recent(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $limit = $request->query('limit', 10);

        $workouts = $this->orchestration->getRecentCompletedWorkouts($student, $limit);

        return response()->json([
            'data' => $workouts->map(function ($workout) {
                return [
                    'id' => $workout->id,
                    'uuid' => $workout->uuid,
                    'plan_day' => $workout->plan_day,
                    'cycle_index' => $workout->cycle_index,
                    'completed_at' => $workout->completed_at?->toIso8601String(),
                    'duration_minutes' => $workout->duration_minutes,
                    'rating' => $workout->rating,
                    'notes' => $workout->notes,
                ];
            })
        ]);
    }
}
