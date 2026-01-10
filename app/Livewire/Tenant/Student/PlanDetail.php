<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\Exercise;

#[Layout('layouts.student')]
class PlanDetail extends Component
{
    public Student $student;
    public StudentPlanAssignment $assignment;

    /** key: exercise_id → ['thumb' => url|null, 'name' => string|null, 'equipment' => string|null] */
    public array $exerciseInfo = [];

    public bool $showExerciseModal = false;
    public array $modalExercise = [];

    public function mount(StudentPlanAssignment $assignment): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) abort(403);

        $this->student = Student::where('email', $user->email)->firstOrFail();

        // Ensure the assignment belongs to current student
        if ($assignment->student_id !== $this->student->id) {
            abort(404);
        }

        $this->assignment = $assignment;

        // Prefetch exercise media/name/equipment for snapshot items
        $days = $this->assignment->exercises_by_day;
        $ids = collect($days)->flatten(1)->pluck('exercise_id')->filter()->unique()->values();
        if ($ids->isNotEmpty()) {
            $exercises = Exercise::whereIn('id', $ids)->get();
            foreach ($exercises as $ex) {
                $images = [];
                foreach ($ex->getMedia('images') as $media) {
                    $images[] = $media->getUrl();
                }
                $this->exerciseInfo[$ex->id] = [
                    'thumb'      => $ex->getFirstMediaUrl('images', 'thumb') ?: null,
                    'images'     => $images,
                    'name'       => $ex->name,
                    'equipment'  => $ex->equipment,
                    'category'   => $ex->category,
                    'level'      => $ex->level,
                    'description'=> $ex->description,
                ];
            }
        }
    }

    public function openExercise(?int $exerciseId = null, array $snapshotItem = []): void
    {
        $exercise = $exerciseId ? Exercise::find($exerciseId) : null;

        $images = [];
        $thumb = null;
        if ($exercise) {
            $thumb = $exercise->getFirstMediaUrl('images', 'thumb') ?: null;
            // Collect all original image URLs
            foreach ($exercise->getMedia('images') as $media) {
                $images[] = $media->getUrl();
            }
        }

        $this->modalExercise = [
            'name'        => $snapshotItem['name'] ?? ($exercise->name ?? 'Ejercicio'),
            'detail'      => $snapshotItem['detail'] ?? null,
            'notes'       => $snapshotItem['notes'] ?? null,
            'category'    => $exercise->category ?? null,
            'equipment'   => $exercise->equipment ?? null,
            'level'       => $exercise->level ?? null,
            'description' => $exercise->description ?? null,
            'images'      => $images,
            'thumb'       => $thumb,
        ];

        $this->showExerciseModal = true;
    }

    public function closeExercise(): void
    {
        $this->showExerciseModal = false;
        $this->modalExercise = [];
    }

    public function render()
    {
        return view('livewire.tenant.student.plan-detail', [
            'assignment' => $this->assignment,
            'exerciseInfo' => $this->exerciseInfo,
            'modalExercise' => $this->modalExercise,
        ]);
    }
}
