<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Models\Tenant\Student;
use App\Models\Tenant\CommercialPlan;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    use WithFileUploads;

    public $avatar;
    public ?string $currentAvatarUrl = null;

    public ?Student $student = null;
    public bool $editMode = false;
    public bool $back = false;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $status = 'prospect';
    public ?int $commercial_plan_id = null;
    public ?string $current_level = null;
    public ?string $billing_frequency = 'monthly';
    public ?string $account_status = 'ok';
    public bool $is_user_enabled = true;
    public ?string $goal = null;

    public array $plans = [];

    /* ------------------------- Bloques JSON ------------------------- */
    public array $personal = [
        'birth_date' => null,
        'gender' => null,
        'height_cm' => null,
        'weight_kg' => null,
    ];

    public array $health = [
        'injuries' => null,
    ];

    public array $training = [
        'experience' => null,
        'days_per_week' => null,
    ];

    public array $communication = [
        'language' => 'es',
        'notifications' => [
            'new_plan' => false,
            'session_reminder' => false,
        ],
    ];

    public array $extra = [
        'emergency_contact' => [
            'name' => '',
            'phone' => '',
        ],
    ];

    /* ---------------------------- Mount ---------------------------- */
    public function mount(?Student $student): void
    {
        $this->plans = CommercialPlan::orderBy('name')->pluck('name', 'id')->toArray();

        if ($student && $student->exists) {
            $this->student = $student;
            $this->fill($student->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'status',
                'commercial_plan_id',
                'current_level',
                'billing_frequency',
                'account_status',
                'is_user_enabled',
                'goal'
            ]));

            $this->personal = $student->personal_data ?? $this->personal;
            $this->health = $student->health_data ?? $this->health;
            $this->training = $student->training_data ?? $this->training;
            $this->communication = $student->communication_data ?? $this->communication;
            $this->extra = $student->extra_data ?? $this->extra;

            $this->editMode = true;
            $this->currentAvatarUrl = $student->getFirstMediaUrl('avatar', 'thumb');
        }
    }

    /* ---------------------------- Reglas ---------------------------- */
    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($this->student?->id)
            ],
            'phone'               => ['nullable', 'string', 'max:30'],
            'status'              => ['required', 'string', 'in:active,paused,inactive,prospect'],
            'commercial_plan_id'  => ['nullable', 'integer', 'exists:commercial_plans,id'],
            'current_level'       => ['nullable', 'string', 'max:50'],
            'billing_frequency'   => ['nullable', 'string', 'in:monthly,yearly'],
            'account_status'      => ['nullable', 'string', 'in:ok,pending,debt'],
            'is_user_enabled'     => ['boolean'],
            'goal'                => ['nullable', 'string', 'max:500'],
            'avatar'              => ['nullable', 'image', 'max:2048'],
        ];
    }

    /* ----------------------------- Save ----------------------------- */
    public function save(): void
    {
        $data = $this->validate();

        $student = $this->student ?? new Student();
        $student->fill($data);

        // Asignar datos JSON
        $student->personal_data = $this->personal;
        $student->health_data = $this->health;
        $student->training_data = $this->training;
        $student->communication_data = $this->communication;
        $student->extra_data = $this->extra;

        $student->save();

        // Imagen
        if ($this->avatar instanceof TemporaryUploadedFile) {
            $student->clearMediaCollection('avatar');
            $student->addMedia($this->avatar->getRealPath())
                ->usingFileName($this->avatar->getClientOriginalName())
                ->toMediaCollection('avatar');
            $this->currentAvatarUrl = $student->getFirstMediaUrl('avatar', 'thumb');
            $this->avatar = null;
        }

        $this->dispatch('saved');

        if ($this->back) {
            redirect()->route('tenant.dashboard.students.index');
            return;
        }

        $this->student = $student;
        $this->editMode = true;
        session()->flash('success', __('students.saved'));
    }

    /* -------------------------- Avatar ops -------------------------- */
    public function deleteAvatar(): void
    {
        if ($this->avatar) {
            $this->avatar = null;
        } elseif ($this->student) {
            $this->student->clearMediaCollection('avatar');
            $this->currentAvatarUrl = null;
        }
    }

    /* ----------------------------- View ----------------------------- */
    public function render()
    {
        return view('livewire.tenant.students.form');
    }
}
