<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\Workout;
use App\Models\Tenant\StudentWeightEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Layout('layouts.student')]
class Progress extends Component
{
    public ?Student $student = null;
    public int $sessionsThisMonth = 0;
    public int $sessionsLastMonth = 0;
    public int $totalSessions = 0;
    public float $adherence = 0;
    public ?float $lastWeight = null;
    public ?float $lastBodyFat = null;
    public float $avgDuration = 0;
    public ?int $avgRating = null;
    public array $recentWorkouts = [];
    public array $monthlyStats = [];
    public ?float $initialWeight = null;
    public ?float $weightChange = null;
    public ?float $currentBMI = null;
    public ?float $initialBMI = null;
    public ?int $age = null;
    public ?string $gender = null;
    public ?float $heightCm = null;
    public array $weightHistory = [];

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $this->student = Student::where('email', $user->email)->firstOrFail();

        $this->loadStats();
    }

    private function loadStats(): void
    {
        // Total de entrenamientos completados
        $this->totalSessions = Workout::where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->count();

        // Entrenamientos este mes
        $this->sessionsThisMonth = Workout::where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', now()->year)
            ->whereMonth('completed_at', now()->month)
            ->count();

        // Entrenamientos mes anterior
        $this->sessionsLastMonth = Workout::where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', now()->subMonth()->year)
            ->whereMonth('completed_at', now()->subMonth()->month)
            ->count();

        // Últimos 10 entrenamientos
        $this->recentWorkouts = Workout::where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($workout) {
                return [
                    'id' => $workout->id,
                    'plan_day' => $workout->plan_day,
                    'completed_at' => $workout->completed_at,
                    'duration_minutes' => $workout->duration_minutes,
                    'rating' => $workout->rating,
                    'notes' => $workout->notes,
                    'exercises_completed' => collect($workout->exercises_data ?? [])->filter(fn($e) => $e['completed'] ?? false)->count(),
                    'total_exercises' => count($workout->exercises_data ?? []),
                ];
            })
            ->toArray();

        // Duración promedio
        $avgDuration = Workout::where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes');
        $this->avgDuration = $avgDuration ? round($avgDuration, 1) : 0;

        // Rating promedio
        $avgRating = Workout::where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->whereNotNull('rating')
            ->avg('rating');
        $this->avgRating = $avgRating ? round($avgRating, 1) : null;

        // Adherencia (workouts completados vs esperados)
        $expectedWorkouts = Workout::where('student_id', $this->student->id)
            ->whereIn('status', ['completed', 'skipped'])
            ->count();
        $this->adherence = $expectedWorkouts > 0 ? round(($this->totalSessions / $expectedWorkouts) * 100, 1) : 0;

        // Datos del estudiante
        $this->initialWeight = $this->student->weight_kg;
        $this->heightCm = $this->student->height_cm;
        $this->gender = $this->student->gender;
        $birthDate = $this->student->birth_date;
        if ($birthDate) {
            $this->age = \Carbon\Carbon::parse($birthDate)->age;
        }

        // IMC inicial
        if ($this->initialWeight && $this->heightCm) {
            $heightM = $this->heightCm / 100;
            $this->initialBMI = round($this->initialWeight / ($heightM ** 2), 1);
        }

        // Último peso registrado - primero intenta StudentWeightEntry
        $lastWeightEntry = StudentWeightEntry::forStudent($this->student->id)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if ($lastWeightEntry) {
            $this->lastWeight = $lastWeightEntry->weight_kg;
        }

        // Obtener historial de pesos desde StudentWeightEntry
        $weightEntries = StudentWeightEntry::forStudent($this->student->id)
            ->orderBy('recorded_at', 'asc')
            ->get();

        if ($weightEntries->count() > 0) {
            $this->weightHistory = $weightEntries->map(function ($entry) {
                return [
                    'date' => $entry->recorded_at->format('Y-m-d'),
                    'weight' => round($entry->weight_kg, 1),
                    'label' => $entry->recorded_at->translatedFormat('d M'),
                    'isInitial' => false,
                ];
            })->toArray();
        } else if (Schema::hasTable('weight_logs')) {
            // Fallback a weight_logs si existen
            $weightLogs = DB::table('weight_logs')
                ->where('student_id', $this->student->id)
                ->orderBy('measured_at', 'asc')
                ->get();

            if ($weightLogs->count() > 0) {
                if (!$this->lastWeight) {
                    $lastLog = $weightLogs->last();
                    $this->lastWeight = $lastLog->weight ?? null;
                }

                $this->weightHistory = $weightLogs->map(function ($log) {
                    return [
                        'date' => \Carbon\Carbon::parse($log->measured_at)->format('Y-m-d'),
                        'weight' => round($log->weight, 1),
                        'label' => \Carbon\Carbon::parse($log->measured_at)->translatedFormat('d M'),
                        'isInitial' => false,
                    ];
                })->toArray();
            }
        }

        // Agregar peso inicial al historial si existe
        if ($this->initialWeight) {
            $initialDate = $this->student->created_at ? $this->student->created_at->format('Y-m-d') : now()->subMonth()->format('Y-m-d');
            $initialEntry = [
                'date' => $initialDate,
                'weight' => round($this->initialWeight, 1),
                'label' => \Carbon\Carbon::parse($initialDate)->translatedFormat('d M'),
                'isInitial' => true,
            ];

            // Si no hay historial, crear solo con el inicial
            if (count($this->weightHistory) === 0) {
                $this->weightHistory[] = $initialEntry;
            } else {
                // Si hay historial, prepend el inicial si no existe
                $firstDate = $this->weightHistory[0]['date'];
                if ($firstDate !== $initialDate) {
                    array_unshift($this->weightHistory, $initialEntry);
                } else {
                    // Marcar el primer entry como inicial
                    $this->weightHistory[0]['isInitial'] = true;
                }
            }
        }

        // Si no hay último peso, usar el inicial
        if (!$this->lastWeight && $this->initialWeight) {
            $this->lastWeight = $this->initialWeight;
        }

        // Calcular cambio de peso
        if ($this->lastWeight && $this->initialWeight) {
            $this->weightChange = round($this->lastWeight - $this->initialWeight, 1);
        }

        // IMC actual
        if ($this->lastWeight && $this->heightCm) {
            $heightM = $this->heightCm / 100;
            $this->currentBMI = round($this->lastWeight / ($heightM ** 2), 1);
        }

        // Estadísticas mensuales (últimos 6 meses)
        $this->monthlyStats = DB::table('workouts')
            ->select(
                DB::raw('YEAR(completed_at) as year'),
                DB::raw('MONTH(completed_at) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(duration_minutes) as avg_duration'),
                DB::raw('AVG(rating) as avg_rating')
            )
            ->where('student_id', $this->student->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($stat) {
                return [
                    'month_name' => \Carbon\Carbon::create($stat->year, $stat->month)->translatedFormat('M Y'),
                    'count' => $stat->count,
                    'avg_duration' => round($stat->avg_duration, 1),
                    'avg_rating' => $stat->avg_rating ? round($stat->avg_rating, 1) : null,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.tenant.student.progress', [
            'student' => $this->student,
            'sessionsThisMonth' => $this->sessionsThisMonth,
            'sessionsLastMonth' => $this->sessionsLastMonth,
            'totalSessions' => $this->totalSessions,
            'adherence' => $this->adherence,
            'lastWeight' => $this->lastWeight,
            'lastBodyFat' => $this->lastBodyFat,
            'avgDuration' => $this->avgDuration,
            'avgRating' => $this->avgRating,
            'recentWorkouts' => $this->recentWorkouts,
            'monthlyStats' => $this->monthlyStats,
            'initialWeight' => $this->initialWeight,
            'weightChange' => $this->weightChange,
            'currentBMI' => $this->currentBMI,
            'initialBMI' => $this->initialBMI,
            'age' => $this->age,
            'gender' => $this->gender,
            'heightCm' => $this->heightCm,
            'weightHistory' => $this->weightHistory,
        ]);
    }
}
