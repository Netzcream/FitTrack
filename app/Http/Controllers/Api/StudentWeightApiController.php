<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentWeightEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentWeightApiController extends Controller
{
    /**
     * GET /api/weight
     *
     * Obtener historial de peso del estudiante (últimas 30 entradas por defecto)
     */
    public function index(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $limit = $request->query('limit', 30);
        $days = $request->query('days'); // Filtrar por últimos N días

        $query = $student->weightEntries()
            ->orderByDesc('recorded_at');

        if ($days && is_numeric($days)) {
            $query->where('recorded_at', '>=', now()->subDays($days));
        }

        $weights = $query
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $weights->map(function ($entry) {
                return $this->formatWeightEntry($entry);
            })->reverse()->values() // Mostrar en orden cronológico (antiguo a nuevo)
        ]);
    }

    /**
     * GET /api/weight/latest
     *
     * Obtener el último registro de peso
     */
    public function latest(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $latest = $student->latestWeight;

        if (!$latest) {
            return response()->json([
                'data' => null,
                'message' => 'No weight entry found'
            ]);
        }

        return response()->json([
            'data' => $this->formatWeightEntry($latest)
        ]);
    }

    /**
     * POST /api/weight
     *
     * Registrar un nuevo peso
     */
    public function store(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'weight_kg' => 'required|numeric|min:20|max:500',
            'recorded_at' => 'sometimes|date|before_or_equal:today',
            'notes' => 'sometimes|string|max:500',
            'source' => 'sometimes|string|in:manual,scale_device,api',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid data',
                'details' => $validator->errors()
            ], 422);
        }

        $entry = StudentWeightEntry::create([
            'student_id' => $student->id,
            'weight_kg' => $request->weight_kg,
            'recorded_at' => $request->recorded_at ? now()->parse($request->recorded_at) : now(),
            'source' => $request->source ?? 'manual',
            'notes' => $request->notes ?? null,
            'meta' => $request->meta ?? [],
        ]);

        return response()->json([
            'message' => 'Weight recorded successfully',
            'data' => $this->formatWeightEntry($entry)
        ], 201);
    }

    /**
     * GET /api/weight/change
     *
     * Obtener cambio de peso en un período (últimos 7 días, 30 días, etc)
     */
    public function change(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $days = $request->query('days', 7); // Default: últimos 7 días

        $change = StudentWeightEntry::weightChangeSince(
            $student->id,
            now()->subDays($days)
        );

        if (!$change) {
            return response()->json([
                'data' => null,
                'message' => 'Not enough data to calculate change'
            ]);
        }

        return response()->json([
            'data' => [
                'period_days' => $change['period_days'],
                'initial_weight_kg' => $change['initial_weight_kg'],
                'current_weight_kg' => $change['current_weight_kg'],
                'change_kg' => round($change['change_kg'], 2),
                'change_percentage' => round($change['change_percentage'], 2),
                'direction' => $change['change_kg'] > 0 ? 'up' : ($change['change_kg'] < 0 ? 'down' : 'stable'),
            ]
        ]);
    }

    /**
     * GET /api/weight/average
     *
     * Obtener peso promedio en un período
     */
    public function average(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $days = $request->query('days', 30);

        $avg = StudentWeightEntry::averageWeightForPeriod(
            $student->id,
            now()->subDays($days),
            now()
        );

        if ($avg === null) {
            return response()->json([
                'data' => null,
                'message' => 'No weight data available for this period'
            ]);
        }

        return response()->json([
            'data' => [
                'period_days' => $days,
                'average_weight_kg' => round($avg, 2),
            ]
        ]);
    }

    /**
     * Formatear una entrada de peso para la respuesta
     */
    private function formatWeightEntry(StudentWeightEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'uuid' => $entry->uuid,
            'weight_kg' => (float) $entry->weight_kg,
            'recorded_at' => $entry->recorded_at->toIso8601String(),
            'source' => $entry->source,
            'notes' => $entry->notes,
            'meta' => $entry->meta,
            'created_at' => $entry->created_at->toIso8601String(),
        ];
    }
}
