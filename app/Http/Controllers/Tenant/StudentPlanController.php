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

        $pdf = Pdf::loadView('pdf.student.training-plan', [
            'plan'     => $plan,
            'student'  => $plan->student,
            'grouped'  => $grouped,
        ])->setPaper('a4');

        $filename = 'Plan - ' . str($plan->name)->slug() . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * New flow: download using assignment (snapshot-based).
     */
    public function downloadAssignment(StudentPlanAssignment $assignment): Response
    {
        $user = Auth::user();
        abort_unless($assignment->student && $assignment->student->email === $user->email, 403);

        $grouped = $assignment->exercises_by_day;

        $pdf = Pdf::loadView('pdf.student.training-plan', [
            'plan'     => $assignment, // The view uses name and grouped only
            'student'  => $assignment->student,
            'grouped'  => $grouped,
        ])->setPaper('a4');

        $filename = 'Plan - ' . str($assignment->name)->slug() . '.pdf';
        return $pdf->download($filename);
    }
}
