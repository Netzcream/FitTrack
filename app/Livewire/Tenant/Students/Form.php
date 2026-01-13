<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Models\Tenant\Student;
use App\Models\Tenant\CommercialPlan;
use App\Models\Tenant\StudentWeightEntry;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
    public ?string $status = 'active';
    public ?int $commercial_plan_id = null;
    public ?string $billing_frequency = 'monthly';
    public ?string $account_status = 'on_time';
    public bool $is_user_enabled = true;
    public ?string $goal = null;

    public array $plans = [];

    /** Datos adicionales unificados */
    public array $data = [
        'birth_date' => null,
        'gender' => null,
        'height_cm' => null,
        'weight_kg' => null,
        'injuries' => null,
        'notifications' => [
            'new_plan' => false,
            'session_reminder' => false,
        ],
        'emergency_contact' => [
            'name' => '',
            'phone' => '',
        ],
    ];

    // Propiedades para el histórico de pesos
    public array $weightHistory = [];
    public ?string $lastWeightDisplay = null;
    public ?float $lastRecordedWeight = null;
    public ?float $newWeight = null;
    public ?string $newWeightDate = null;
    public ?string $newWeightNotes = null;

    public function getCurrentPlanProperty()
    {
        if (!$this->student) {
            return null;
        }

        return $this->student->currentPlanAssignment()->with('plan')->first();
    }

    #[\Livewire\Attributes\On('plan-assigned')]
    public function refreshPlan(): void
    {
        if ($this->student) {
            $this->student->refresh();
        }
    }

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
                'billing_frequency',
                'account_status',
                'is_user_enabled',
                'goal',
            ]));

            $this->data = array_replace_recursive($this->data, $student->data ?? []);

            $this->editMode = true;
            $this->currentAvatarUrl = $student->getFirstMediaUrl('avatar', 'thumb');

            // Cargar el historial de pesos
            $this->loadWeightHistory();
        } else {
            // Para nuevo estudiante, inicializar lastWeightDisplay vacío
            $this->lastWeightDisplay = '—';
        }

        // Inicializar fecha con hoy
        $this->newWeightDate = now()->format('Y-m-d');
    }

    private function loadWeightHistory(): void
    {
        if (!$this->student) {
            return;
        }

        $weightEntries = StudentWeightEntry::forStudent($this->student->id)
            ->orderBy('recorded_at', 'desc')
            ->take(15)
            ->get();

        $this->weightHistory = $weightEntries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'weight' => round($entry->weight_kg, 1),
                'date' => $entry->recorded_at->format('d/m/Y'),
                'notes' => $entry->notes,
            ];
        })->toArray();

        // Establecer el último peso como display
        if (count($this->weightHistory) > 0) {
            $this->lastWeightDisplay = $this->weightHistory[0]['weight'] . ' kg (' . $this->weightHistory[0]['date'] . ')';
            $this->lastRecordedWeight = $this->weightHistory[0]['weight'];
        } else {
            $this->lastWeightDisplay = $this->data['weight_kg'] ? round($this->data['weight_kg'], 1) . ' kg (inicial)' : '—';
            $this->lastRecordedWeight = $this->data['weight_kg'] ? (float) $this->data['weight_kg'] : null;
        }
    }

    /**
     * Obtener historial de pesos para el gráfico (ordenado ascendentemente)
     */
    public function getWeightChartDataProperty(): array
    {
        if (!$this->student) {
            return [];
        }

        $weightEntries = StudentWeightEntry::forStudent($this->student->id)
            ->orderBy('recorded_at', 'asc')
            ->take(50)
            ->get();

        return $weightEntries->map(function ($entry) {
            return [
                'date' => $entry->recorded_at->format('Y-m-d'),
                'weight' => round($entry->weight_kg, 1),
                'label' => $entry->recorded_at->translatedFormat('d M'),
                'isInitial' => false,
            ];
        })->toArray();
    }

    public function addWeightEntry(): void
    {
        $this->validate([
            'newWeight' => ['required', 'numeric', 'min:20', 'max:300'],
            'newWeightDate' => ['required', 'date'],
        ]);

        if (!$this->student) {
            return;
        }

        StudentWeightEntry::create([
            'student_id' => $this->student->id,
            'weight_kg' => $this->newWeight,
            'recorded_at' => $this->newWeightDate,
            'source' => 'manual',
            'notes' => $this->newWeightNotes,
        ]);

        // Recargar el historial
        $this->loadWeightHistory();

        // Limpiar el formulario
        $this->newWeight = null;
        $this->newWeightNotes = null;
        $this->newWeightDate = now()->format('Y-m-d');

        session()->flash('success', 'Peso registrado correctamente');

        // Forzar cierre del modal en el siguiente render
        $this->dispatch('weight-added');
    }

    public function deleteWeightEntry(int $entryId): void
    {
        $entry = StudentWeightEntry::find($entryId);
        if ($entry && $entry->student_id === $this->student->id) {
            $entry->delete();
            $this->loadWeightHistory();
            session()->flash('success', 'Registro de peso eliminado');
        }
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($this->student?->id),
            ],
            'phone'              => ['nullable', 'string', 'max:30'],
            'status'             => ['required', 'string', 'in:active,paused,inactive,prospect'],
            'commercial_plan_id' => ['nullable', 'integer', 'exists:commercial_plans,id'],
            'billing_frequency'  => ['required', 'string', 'in:monthly,quarterly,yearly'],
            'account_status'     => ['required', 'string', 'in:on_time,due,review'],
            'is_user_enabled'    => ['boolean'],
            'goal'               => ['nullable', 'string', 'max:500'],
            'avatar'             => ['nullable', 'image', 'max:2048'],

            'data.birth_date'                 => ['nullable', 'date'],
            'data.gender'                     => ['nullable', 'in:male,female,other'],
            'data.height_cm'                  => ['nullable', 'numeric', 'min:0'],
            'data.weight_kg'                  => ['nullable', 'numeric', 'min:0'],
            'data.injuries'                   => ['nullable', 'string', 'max:500'],
            'data.notifications.new_plan'     => ['boolean'],
            'data.notifications.session_reminder' => ['boolean'],
            'data.emergency_contact.name'     => ['nullable', 'string', 'max:150'],
            'data.emergency_contact.phone'    => ['nullable', 'string', 'max:30'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $user = $this->ensureUser();

        $student = $this->student ?? new Student();
        $student->fill([
            'user_id' => $user->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'commercial_plan_id' => $this->commercial_plan_id,
            'billing_frequency' => $this->billing_frequency,
            'account_status' => $this->account_status,
            'is_user_enabled' => $this->is_user_enabled,
            'goal' => $this->goal,
        ]);

        // Limpiar datos: convertir cadenas vacías en null para campos numéricos
        $cleanedData = $this->data;
        if (isset($cleanedData['height_cm']) && $cleanedData['height_cm'] === '') {
            $cleanedData['height_cm'] = null;
        }
        if (isset($cleanedData['weight_kg']) && $cleanedData['weight_kg'] === '') {
            $cleanedData['weight_kg'] = null;
        }
        if (isset($cleanedData['birth_date']) && $cleanedData['birth_date'] === '') {
            $cleanedData['birth_date'] = null;
        }

        $student->data = $cleanedData;
        $student->save();

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

        if (! $this->editMode) {
            redirect()->route('tenant.dashboard.students.edit', $student->uuid);
            return;
        }

        $this->student = $student;
        $this->editMode = true;
        session()->flash('success', __('students.saved'));
    }

    protected function ensureUser(): User
    {
        $name = trim($this->first_name . ' ' . $this->last_name) ?: $this->email;

        if ($this->student && $this->student->user) {
            $user = $this->student->user;
        } else {
            $user = User::where('email', $this->email)->first();
        }

        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $this->email,
                'password' => Str::random(20),
            ]);
        } else {
            $user->name = $name;
            if ($user->email !== $this->email) {
                $user->email = $this->email;
            }
            $user->save();
        }

        $role = Role::firstOrCreate(['name' => 'Alumno']);
        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        return $user;
    }

    public function deleteAvatar(): void
    {
        if ($this->avatar) {
            $this->avatar = null;
            return;
        }

        if ($this->student) {
            $this->student->clearMediaCollection('avatar');
            $this->currentAvatarUrl = null;
        }
    }

    public function render()
    {
        return view('livewire.tenant.students.form');
    }
}
