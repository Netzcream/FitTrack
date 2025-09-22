<?php

namespace App\Livewire\Tenant\Students\Health;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Tenant\Student;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant.students.settings')]
class Index extends Component
{
    use WithFileUploads;

    public Student $student;
    public $active = 'health';
    public bool $back = false;

    // --- Apto / PAR-Q ---
    public ?string $apt_fitness_status = null;
    public ?string $apt_fitness_expires_at = null;
    public $aptFile; // temp file (img/pdf)

    public ?string $parq_result = null;
    public ?string $parq_date = null;

    // --- Arrays con patrón "Agregar + listado" ---
    public array $injuries = [];
    public string $injuryInput = '';

    public array $medical_history = [];
    public string $medicalHistoryInput = '';

    public array $medications_allergies = [];
    public string $medAllergyInput = '';

    // --- Emergencia & consentimientos ---
    public array $emergency_contact = ['name' => null, 'relation' => null, 'phone' => null];

    public ?string $tos_accepted_at = null;
    public ?string $sensitive_data_consent_at = null;
    public bool $image_consent = false;
    public ?string $image_consent_at = null;

    public function mount(Student $student): void
    {
        $this->student = $student;

        foreach (
            [
                'apt_fitness_status',
                'apt_fitness_expires_at',
                'parq_result',
                'parq_date',
                'tos_accepted_at',
                'sensitive_data_consent_at',
                'image_consent',
                'image_consent_at',

            ] as $f
        ) {
            $this->$f = $student->$f;
        }

        foreach (
            [
                'apt_fitness_status',
                // estas 2 se setean abajo con formato
                'apt_fitness_expires_at',
                'parq_result',
                // 'parq_date',
                // datetimes abajo
                // 'tos_accepted_at',
                // 'sensitive_data_consent_at',
                'image_consent',
                // 'image_consent_at',
            ] as $f
        ) {
            $this->$f = $student->$f;
        }

        $this->apt_fitness_expires_at   = $this->asDate($student->apt_fitness_expires_at);
        $this->parq_date                = $this->asDate($student->parq_date);
        $this->tos_accepted_at          = $this->asDateTime($student->tos_accepted_at);
        $this->sensitive_data_consent_at = $this->asDateTime($student->sensitive_data_consent_at);
        $this->image_consent_at         = $this->asDateTime($student->image_consent_at);

        // Arrays desde DB (nunca null)
        $this->injuries = (array) ($student->injuries ?? []);
        $this->medical_history = (array) ($student->medical_history ?? []);
        $this->medications_allergies = (array) ($student->medications_allergies ?? []);

        // Emergencia con defaults
        $this->emergency_contact = array_merge(
            ['name' => null, 'relation' => null, 'phone' => null],
            (array) ($student->emergency_contact ?? [])
        );
    }

    // ---- Add/Remove helpers (listas) ----
    public function addInjury(): void
    {
        $v = trim($this->injuryInput);
        if ($v !== '') {
            $this->injuries[] = $v;
            $this->injuryInput = '';
        }
    }
    public function removeInjury(int $index): void
    {
        unset($this->injuries[$index]);
        $this->injuries = array_values($this->injuries);
    }

    public function addMedicalHistory(): void
    {
        $v = trim($this->medicalHistoryInput);
        if ($v !== '') {
            $this->medical_history[] = $v;
            $this->medicalHistoryInput = '';
        }
    }
    public function removeMedicalHistory(int $index): void
    {
        unset($this->medical_history[$index]);
        $this->medical_history = array_values($this->medical_history);
    }

    public function addMedAllergy(): void
    {
        $v = trim($this->medAllergyInput);
        if ($v !== '') {
            $this->medications_allergies[] = $v;
            $this->medAllergyInput = '';
        }
    }
    public function removeMedAllergy(int $index): void
    {
        unset($this->medications_allergies[$index]);
        $this->medications_allergies = array_values($this->medications_allergies);
    }
    protected function asDate($v): ?string
    {
        return $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : null;
    }

    protected function asDateTime($v): ?string
    {
        // para <input type="datetime-local">
        return $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d\TH:i') : null;
    }

    protected function nullIfEmpty($v)
    {
        return ($v === '' || $v === null) ? null : $v;
    }

    // ---- Validación ----
    public function rules(): array
    {
        return [
            'apt_fitness_status'         => ['nullable', Rule::in(['valid', 'expired', 'not_required'])],
            'apt_fitness_expires_at'     => ['nullable', 'date'],
            'aptFile'                    => ['nullable', \Illuminate\Validation\Rules\File::types(['jpg', 'jpeg', 'png', 'webp', 'pdf'])->max(8192)],

            'parq_result'                => ['nullable', Rule::in(['fit', 'refer_to_md'])],
            'parq_date'                  => ['nullable', 'date'],

            'injuries'                   => ['array'],
            'medical_history'            => ['array'],
            'medications_allergies'      => ['array'],

            'emergency_contact'          => ['array'],
            'emergency_contact.name'     => ['nullable', 'string', 'max:120'],
            'emergency_contact.relation' => ['nullable', 'string', 'max:120'],
            'emergency_contact.phone'    => ['nullable', 'string', 'max:100'],

            'tos_accepted_at'            => ['nullable', 'date'],
            'sensitive_data_consent_at'  => ['nullable', 'date'],
            'image_consent'              => ['boolean'],
            'image_consent_at'           => ['nullable', 'date'],
        ];
    }

    // ---- Guardar ----
    public function save()
    {
        $data = $this->validate();


        $data['apt_fitness_expires_at']     = $this->nullIfEmpty($this->apt_fitness_expires_at);
        $data['parq_date']                  = $this->nullIfEmpty($this->parq_date);
        $data['tos_accepted_at']            = $this->nullIfEmpty($this->tos_accepted_at);
        $data['sensitive_data_consent_at']  = $this->nullIfEmpty($this->sensitive_data_consent_at);
        $data['image_consent_at']           = $this->nullIfEmpty($this->image_consent_at);

        // Persistir columnas fillable (arrays incluidos; están casteados en el modelo)
        $this->student->fill($data)->save();

        // Media: archivo de apto en colección 'apto'
        if ($this->aptFile) {
            $this->student->clearMediaCollection('apto');
            $this->student->addMedia($this->aptFile->getRealPath())
                ->usingFileName($this->aptFile->getClientOriginalName())
                ->toMediaCollection('apto');
            $this->reset('aptFile');
        }

        // Feedback
        session()->flash('success', __('site.student_updated'));
        $this->dispatch('updated');

        // Refrescar estado local
        $this->mount($this->student->fresh());

        return $this->back
            ? $this->redirect(route('tenant.dashboard.students.index'), navigate: true)
            : $this->redirect(route('tenant.dashboard.students.health', $this->student), navigate: true);
    }

    public function removeTempApto(): void
    {
        $this->aptFile = null;
    }

    public function removeApto(): void
    {
        $this->student->clearMediaCollection('apto');
        $this->student->refresh();
    }

    public function render()
    {
        /** @var \Illuminate\View\View $view */
        $view = view('livewire.tenant.students.health.index');

        $aptInDays = optional($this->student->apt_fitness_expires_at)?->diffInDays(now(), false);

        return $view->layoutData([
            'student'          => $this->student,
            'active'           => 'health',
            'overdueInvoices'  => 0,
            'aptExpiresInDays' => ($aptInDays !== null && $aptInDays >= 0) ? $aptInDays : null,
            'unreadMessages'   => 0,
        ]);
    }
}
