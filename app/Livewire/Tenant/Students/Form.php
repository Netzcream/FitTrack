<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use App\Models\Tenant\Student;
use App\Models\Tenant\Tag;
use App\Models\Tenant\CommercialPlan;
use App\Models\Tenant\TrainingGoal;
use App\Models\Tenant\TrainingPhase;
use App\Models\Tenant\CommunicationChannel;
use App\Models\Tenant\PaymentMethod;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    use WithFileUploads;

    public ?int $id = null;
    public bool $editMode = false;
    public bool $back = true;

    /* --------- (resto de props como ya tenías) --------- */
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $status = 'active';
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $timezone = 'America/Argentina/Buenos_Aires'; // default AR
    public array $timezones = [];
    public ?string $birth_date = null;
    public ?string $gender = null;
    public ?float $height_cm = null;
    public ?float $weight_kg = null;
    public bool $is_user_enabled = false;
    public ?string $language = 'es';
    // Spatie + Livewire
    public $avatar;       // temp file
    public $aptFile;      // temp file

    public ?int $primary_training_goal_id = null;
    public array $secondary_goals = [];
    public ?string $availability_text = null;
    public array $training_preferences = [];

    public array $injuries = [];
    public array $medical_history = [];
    public array $medications_allergies = [];

    public ?string $apt_fitness_status = null;
    public ?string $apt_fitness_expires_at = null;
    public ?string $parq_result = null;
    public ?string $parq_date = null;

    public ?float $last_weight_kg = null;
    public ?float $last_body_fat_pct = null;
    public ?float $last_muscle_pct = null;
    public ?float $girth_waist_cm = null;
    public ?float $girth_hip_cm = null;
    public ?float $girth_chest_cm = null;
    public ?float $girth_arm_cm = null;
    public ?float $girth_thigh_cm = null;

    public ?string $current_level = null;
    public ?string $experience_summary = null;

    public ?int $current_training_phase_id = null;
    public ?string $plan_start_date = null;
    public ?string $plan_end_date = null;

    public ?int $total_sessions = null;
    public ?float $avg_adherence_pct = null;
    public ?string $highlight_prs = null;

    public ?int $preferred_channel_id = null;
    public array $notifications = ['marketing' => true, 'reminders' => true, 'news' => true];

    public ?int $commercial_plan_id = null;
    public ?string $billing_frequency = null;
    public ?int $preferred_payment_method_id = null;
    public ?string $account_status = null;
    public ?string $lead_source = null;
    public ?string $private_notes = null;

    public ?string $tos_accepted_at = null;
    public ?string $sensitive_data_consent_at = null;
    public bool $image_consent = false;
    public ?string $image_consent_at = null;

    public array $emergency_contact = ['name' => null, 'relation' => null, 'phone' => null];
    public array $links_json = [];

    /* -------- Tags UX (typeahead + chips) -------- */
    public string $tagQuery = '';
    public array $tagSuggestions = [];       // [{id,name,color,code}]
    public array $selectedTags = [];         // [{id,name,color,code}]

    public function mount(?Student $student): void
    {
        if ($student && $student->exists) {
            $this->editMode = true;
            $this->id = (int) $student->id;

            foreach (
                [
                    'first_name',
                    'last_name',
                    'status',
                    'email',
                    'phone',
                    'timezone',
                    'birth_date',
                    'gender',
                    'height_cm',
                    'weight_kg',
                    'is_user_enabled',
                    'language',
                    'primary_training_goal_id',
                    'availability_text',
                    'apt_fitness_status',
                    'apt_fitness_expires_at',
                    'parq_result',
                    'parq_date',
                    'last_weight_kg',
                    'last_body_fat_pct',
                    'last_muscle_pct',
                    'girth_waist_cm',
                    'girth_hip_cm',
                    'girth_chest_cm',
                    'girth_arm_cm',
                    'girth_thigh_cm',
                    'current_level',
                    'experience_summary',
                    'current_training_phase_id',
                    'plan_start_date',
                    'plan_end_date',
                    'total_sessions',
                    'avg_adherence_pct',
                    'highlight_prs',
                    'preferred_channel_id',
                    'commercial_plan_id',
                    'billing_frequency',
                    'preferred_payment_method_id',
                    'account_status',
                    'lead_source',
                    'private_notes',
                    'tos_accepted_at',
                    'sensitive_data_consent_at',
                    'image_consent',
                    'image_consent_at',
                ] as $attr
            ) {
                $this->$attr = $student->$attr;
            }

            // Campos array: jamás null
            $this->secondary_goals       = (array) ($student->secondary_goals ?? []);
            $this->training_preferences  = (array) ($student->training_preferences ?? []);
            $this->injuries              = (array) ($student->injuries ?? []);
            $this->medical_history       = (array) ($student->medical_history ?? []);
            $this->medications_allergies = (array) ($student->medications_allergies ?? []);
            $this->notifications         = array_merge(['marketing' => true, 'reminders' => true, 'news' => true], (array) ($student->notifications ?? []));
            $this->links_json            = (array) ($student->links_json ?? []);
            $this->emergency_contact     = array_merge(['name' => null, 'relation' => null, 'phone' => null], (array) ($student->emergency_contact ?? []));
            if ($student && $student->exists) {
                $this->timezone = $student->timezone ?: 'America/Argentina/Buenos_Aires';
            } else {
                $this->timezone = $this->timezone ?: 'America/Argentina/Buenos_Aires';
            }

            // Tags seleccionados
            $this->selectedTags = $student->tags()
                ->select('tags.id', 'tags.name', 'tags.code', 'tags.color') // ← calificado
                ->orderBy('tags.name', 'asc')                               // ← calificado
                ->get()
                ->map->toArray()
                ->all();
        }

        $this->timezones = $this->buildTimezoneOptions();
    }


    // Avatar
    public function removeTempAvatar(): void
    {
        $this->avatar = null;
    }
    public function removeAvatar(): void
    {
        if ($this->editMode && $this->id) {
            optional(\App\Models\Tenant\Student::find($this->id))
                ?->clearMediaCollection('avatar');
        }
    }

    // Apto
    public function removeTempApto(): void
    {
        $this->aptFile = null;
    }
    public function removeApto(): void
    {
        if ($this->editMode && $this->id) {
            optional(\App\Models\Tenant\Student::find($this->id))
                ?->clearMediaCollection('apto');
        }
    }


    /* ---------------- Rules ---------------- */
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'status'     => ['required', Rule::in(['active', 'paused', 'inactive', 'prospect'])],
            'email'      => ['nullable', 'email', 'max:190'],
            'phone'      => ['nullable', 'string', 'max:100'],
            'timezone'   => ['nullable', 'string', 'max:64'],
            'birth_date' => ['nullable', 'date'],
            'gender'     => ['nullable', Rule::in(['male', 'female', 'non_binary', 'other'])],
            'height_cm'  => ['nullable', 'numeric', 'between:0,500'],
            'weight_kg'  => ['nullable', 'numeric', 'between:0,500'],
            'is_user_enabled' => ['boolean'],
            'language' => ['nullable', Rule::in(['es', 'en'])],
            // Media (usamos File rules para permitir imágenes/documentos)
            'avatar'  => ['nullable', \Illuminate\Validation\Rules\File::image()->max(4096)],
            'aptFile' => ['nullable', \Illuminate\Validation\Rules\File::types(['jpg', 'jpeg', 'png', 'webp', 'pdf'])->max(8192)],

            'primary_training_goal_id' => ['nullable', 'exists:training_goals,id'],
            'secondary_goals' => ['array'],
            'availability_text' => ['nullable', 'string', 'max:2000'],
            'training_preferences' => ['array'],

            'injuries' => ['array'],
            'medical_history' => ['array'],
            'medications_allergies' => ['array'],

            'apt_fitness_status' => ['nullable', Rule::in(['valid', 'expired', 'not_required'])],
            'apt_fitness_expires_at' => ['nullable', 'date'],
            'parq_result' => ['nullable', Rule::in(['fit', 'refer_to_md'])],
            'parq_date' => ['nullable', 'date'],

            'last_weight_kg' => ['nullable', 'numeric', 'between:0,500'],
            'last_body_fat_pct' => ['nullable', 'numeric', 'between:0,100'],
            'last_muscle_pct'   => ['nullable', 'numeric', 'between:0,100'],
            'girth_waist_cm' => ['nullable', 'numeric', 'between:0,500'],
            'girth_hip_cm'   => ['nullable', 'numeric', 'between:0,500'],
            'girth_chest_cm' => ['nullable', 'numeric', 'between:0,500'],
            'girth_arm_cm'   => ['nullable', 'numeric', 'between:0,500'],
            'girth_thigh_cm' => ['nullable', 'numeric', 'between:0,500'],

            'current_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'experience_summary' => ['nullable', 'string', 'max:2000'],

            'current_training_phase_id' => ['nullable', 'exists:training_phases,id'],
            'plan_start_date' => ['nullable', 'date'],
            'plan_end_date'   => ['nullable', 'date', 'after_or_equal:plan_start_date'],

            'total_sessions' => ['nullable', 'integer', 'min:0'],
            'avg_adherence_pct' => ['nullable', 'numeric', 'between:0,100'],
            'highlight_prs' => ['nullable', 'string', 'max:2000'],

            'preferred_channel_id' => ['nullable', 'exists:communication_channels,id'],
            'notifications' => ['array'],

            'commercial_plan_id' => ['nullable', 'exists:commercial_plans,id'],
            'billing_frequency'  => ['nullable', Rule::in(['monthly', 'quarterly', 'yearly'])],
            'preferred_payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'account_status' => ['nullable', Rule::in(['on_time', 'due', 'review'])],
            'lead_source' => ['nullable', 'string', 'max:120'],
            'private_notes' => ['nullable', 'string'],

            'tos_accepted_at' => ['nullable', 'date'],
            'sensitive_data_consent_at' => ['nullable', 'date'],
            'image_consent' => ['boolean'],
            'image_consent_at' => ['nullable', 'date'],

            'emergency_contact' => ['array'],
            'emergency_contact.name' => ['nullable', 'string', 'max:120'],
            'emergency_contact.relation' => ['nullable', 'string', 'max:120'],
            'emergency_contact.phone' => ['nullable', 'string', 'max:100'],
        ];
    }
    protected function buildTimezoneOptions(): array
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $ids = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $out = [];

        foreach ($ids as $id) {
            $tz = new \DateTimeZone($id);
            $offset = $tz->getOffset($now); // segundos
            $sign = $offset >= 0 ? '+' : '-';
            $abs  = abs($offset);
            $h = str_pad((string) floor($abs / 3600), 2, '0', STR_PAD_LEFT);
            $m = str_pad((string) floor(($abs % 3600) / 60), 2, '0', STR_PAD_LEFT);
            $label = "(GMT{$sign}{$h}:{$m}) {$id}";
            $out[] = ['id' => $id, 'label' => $label];
        }

        // ordenamos por etiqueta (offset primero y luego nombre)
        usort($out, fn($a, $b) => strcmp($a['label'], $b['label']));
        return $out;
    }
    /* ---------------- Tags: búsqueda/alta/selección ---------------- */
    public function updatedTagQuery(): void
    {
        $q = trim($this->tagQuery);
        if ($q === '') {
            $this->tagSuggestions = [];
            return;
        }

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
        $this->tagSuggestions = Tag::query()
            ->where(
                fn($qq) =>
                $qq->where('name', 'like', $like)
                    ->orWhere('code', 'like', $like)
            )
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'code', 'color'])
            ->map->toArray()
            ->all();
    }

    public function selectTag(int $id): void
    {
        // Si ya lo tenemos, no duplicar
        if (collect($this->selectedTags)->firstWhere('id', $id)) return;

        if ($tag = Tag::find($id, ['id', 'name', 'code', 'color'])) {
            $this->selectedTags[] = $tag->toArray();
        }
        $this->tagQuery = '';
        $this->tagSuggestions = [];
    }

    public function addTagFromQuery(): void
    {
        $name = trim($this->tagQuery);
        if ($name === '') return;

        // ¿ya existe por nombre o code slug?
        $codeBase = Str::slug($name);
        $existing = Tag::where('name', $name)
            ->orWhere('code', $codeBase)
            ->first(['id', 'name', 'code', 'color']);

        if ($existing) {
            // sólo agregar a seleccionados
            if (!collect($this->selectedTags)->firstWhere('id', $existing->id)) {
                $this->selectedTags[] = $existing->toArray();
            }
            $this->tagQuery = '';
            $this->tagSuggestions = [];
            return;
        }

        // crear nuevo: color random y code único con fallback
        $color = $this->randomColor();
        $code  = $this->uniqueCode($codeBase);

        $tag = new Tag([
            'name'      => $name,
            'code'      => $code,
            'color'     => $color,
            'is_active' => true,
        ]);
        $tag->save();

        $this->selectedTags[] = $tag->only(['id', 'name', 'code', 'color']);
        $this->tagQuery = '';
        $this->tagSuggestions = [];
    }

    public function removeTag(int $id): void
    {
        $this->selectedTags = array_values(
            array_filter($this->selectedTags, fn($t) => (int)$t['id'] !== (int)$id)
        );
    }

    protected function randomColor(): string
    {
        // paleta legible
        $palette = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#EC4899'];
        return $palette[array_rand($palette)];
    }

    protected function uniqueCode(string $base): string
    {
        $base = $base !== '' ? $base : 'tag';
        $code = $base;
        $n = 2;
        while (Tag::withTrashed()->where('code', $code)->exists()) {
            $code = $base . '-' . $n;
            $n++;
        }
        return $code;
    }

    /* ---------------- Guardar ---------------- */
    public function save()
    {
        $data = $this->validate();

        $student = $this->editMode
            ? Student::findOrFail($this->id)
            : new Student();

        $student->fill($data);
        $student->save();

        // Media (Spatie) - avatar preview con Livewire funciona con temporaryUrl()
        if ($this->avatar) {
            $student->clearMediaCollection('avatar');
            $student->addMedia($this->avatar->getRealPath())
                ->usingFileName($this->avatar->getClientOriginalName())
                ->toMediaCollection('avatar');
        }
        if ($this->aptFile) {
            $student->clearMediaCollection('apto');
            $student->addMedia($this->aptFile->getRealPath())
                ->usingFileName($this->aptFile->getClientOriginalName())
                ->toMediaCollection('apto');
        }

        // Tags: sync por ids
        $ids = collect($this->selectedTags)->pluck('id')->all();
        $student->tags()->sync($ids);

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('site.student_updated'));
            $this->mount($student->fresh());
        } else {
            session()->flash('success', __('site.student_created'));
        }

        return $this->back
            ? $this->redirect(route('tenant.dashboard.students.index'), navigate: true)
            : $this->redirect(route('tenant.dashboard.students.edit', $student), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.students.form', [
            'plans'   => CommercialPlan::orderBy('name')->get(['id', 'name']),
            'goals'   => TrainingGoal::orderBy('name')->get(['id', 'name']),
            'phases'  => TrainingPhase::orderBy('name')->get(['id', 'name']),
            'channels' => CommunicationChannel::orderBy('name')->get(['id', 'name']),
            'methods' => PaymentMethod::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
