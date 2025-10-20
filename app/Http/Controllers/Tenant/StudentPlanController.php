<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TrainingPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class StudentPlanController extends Controller
{
    public function download(TrainingPlan $plan): Response
    {
        $user = Auth::user();

        // Seguridad: solo el alumno dueño puede ver su plan
        abort_unless($plan->student && $plan->student->email === $user->email, 403);

        $plan->load(['exercises.media', 'student']);

        // Agrupar ejercicios por día
        $grouped = $plan->exercises->groupBy(fn($ex) => $ex->pivot->day ?? 0)->sortKeys();

        $pdf = Pdf::loadView('pdf.student.training-plan', [
            'plan'     => $plan,
            'student'  => $plan->student,
            'grouped'  => $grouped,
        ])->setPaper('a4');

        $filename = 'Plan - ' . str($plan->name)->slug() . '.pdf';
        return $pdf->download($filename);
    }
}
