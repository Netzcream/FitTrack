<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\StudentPlanAssignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class StudentPlanController extends Controller
{
    /**
     * Backward-compatible method (kept if some URLs still hit /plan/{plan}/download)
     * Used also by trainers in the dashboard to download plans
     */
    public function download(TrainingPlan $plan): Response
    {
        $user = Auth::user();

        // Seguridad: solo el alumno dueño puede ver su plan (si student_id está presente)
        // Si no tiene student_id, es una plantilla pública que cualquier trainer puede descargar
        if ($plan->student) {
            abort_unless($plan->student->email === $user->email, 403);
        }

        // Agrupar ejercicios por día desde JSON (modelo actual)
        $grouped = collect($plan->exercises_data ?? [])->groupBy(fn ($ex) => $ex['day'] ?? 0)->sortKeys();

        // Cargar los ejercicios completos con sus imágenes
        $exerciseIds = collect($plan->exercises_data ?? [])
            ->pluck('exercise_id')
            ->filter()
            ->unique()
            ->values();

        $exercises = \App\Models\Tenant\Exercise::whereIn('id', $exerciseIds)->get()->keyBy('id');

        // Agregar los objetos Exercise completos a cada item
        $groupedEnriched = $grouped->map(function ($dayExercises) use ($exercises) {
            return collect($dayExercises)->map(function ($item) use ($exercises) {
                $exerciseId = $item['exercise_id'] ?? null;
                $exercise = $exerciseId ? $exercises->get($exerciseId) : null;
                return (object) array_merge($item, [
                    'exercise' => $exercise,
                ]);
            });
        });

        // Obtener logo del tenant
        $logo = tenant()->config?->getFirstMediaUrl('logo');
        if (!$logo) {
            $logo = public_path('images/logo-placeholder.png');
        }

        $pdf = Pdf::loadView('pdf.student.training-plan', [
            'plan'       => $plan,
            'student'    => $plan->student,
            'grouped'    => $groupedEnriched,
            'logo'       => $logo,
            'colorBase'  => tenant_config('color_base', '#263d83'),
            'colorDark'  => tenant_config('color_dark', '#1e2a5e'),
        ])->setPaper('a4');

        $filename = 'Plan - ' . str($plan->name)->slug() . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * New flow: download using assignment (snapshot-based).
     * Can be accessed by both students (their own plans) and trainers (any student's plan).
     */
    public function downloadAssignment(StudentPlanAssignment $assignment): Response
    {
        $user = Auth::user();

        // Allow if user is the student OR if user is a trainer (any role except 'Alumno')
        $isStudent = $user->hasRole('Alumno');
        if ($isStudent) {
            // Students can only download their own plans
            abort_unless($assignment->student && $assignment->student->email === $user->email, 403);
        }
        // Trainers can download any student's plan (no additional check needed)

        return $this->buildAssignmentPdfResponse($assignment);
    }

    /**
     * Public signed URL download (sin sesión).
     */
    public function downloadAssignmentPublic(StudentPlanAssignment $assignment): Response
    {
        return $this->buildAssignmentPdfResponse($assignment);
    }

    private function buildAssignmentPdfResponse(StudentPlanAssignment $assignment): Response
    {

        $grouped = $assignment->exercises_by_day;

        // Cargar los ejercicios completos con sus imágenes
        $exerciseIds = collect($assignment->exercises_snapshot ?? [])
            ->pluck('exercise_id')
            ->filter()
            ->unique()
            ->values();

        $exercises = \App\Models\Tenant\Exercise::whereIn('id', $exerciseIds)->get()->keyBy('id');

        // Agregar los objetos Exercise completos a cada item del snapshot
        $groupedEnriched = $grouped->map(function ($dayExercises) use ($exercises) {
            return collect($dayExercises)->map(function ($item) use ($exercises) {
                $exerciseId = $item['exercise_id'] ?? null;
                $exercise = $exerciseId ? $exercises->get($exerciseId) : null;
                return (object) array_merge($item, [
                    'exercise' => $exercise, // Objeto completo con media
                ]);
            });
        });

        // Obtener logo del tenant
        $logo = tenant()->config?->getFirstMediaUrl('logo');
        if (!$logo) {
            // Fallback: generar un logo temporal si no existe
            $logo = public_path('images/logo-placeholder.png');
        }

        $pdf = Pdf::loadView('pdf.student.training-plan', [
            'assignment' => $assignment,
            'student'    => $assignment->student,
            'grouped'    => $groupedEnriched,
            'logo'       => $logo,
            'colorBase'  => tenant_config('color_base', '#263d83'),
            'colorDark'  => tenant_config('color_dark', '#1e2a5e'),
        ])->setPaper('a4');

        $filename = 'Plan - ' . str($assignment->name)->slug() . '.pdf';
        return $pdf->download($filename);
    }
}
