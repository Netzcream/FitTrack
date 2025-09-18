<?php

namespace App\Livewire\Tenant\Students\Profile;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Livewire\Concerns\BuildsTimezones;
use App\Livewire\Concerns\ManagesStudentTags;
use App\Models\Tenant\{Student, TrainingGoal, CommunicationChannel};
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant.students.settings')]
class Form extends Component
{
    use WithFileUploads, BuildsTimezones, ManagesStudentTags;

    public Student $student;

    // Props de esta pestaña
    public ?string $first_name = null, $last_name = null, $document_number = null;
    public ?string $email = null, $phone = null, $status = 'active';
    public ?string $timezone = 'America/Argentina/Buenos_Aires', $birth_date = null, $gender = null, $language = 'es';
    public bool $is_user_enabled = false;
    public $avatar;
    public array $timezones = [];
    public ?int $primary_training_goal_id = null, $preferred_channel_id = null;
    public ?float $height_cm = null, $weight_kg = null;
    public ?string $availability_text = null, $experience_summary = null;

    public function mount(Student $student): void
    {
        $this->student = $student;

        foreach ([
            'first_name','last_name','document_number','email','phone','status','timezone','birth_date',
            'gender','language','is_user_enabled','primary_training_goal_id','height_cm','weight_kg',
            'availability_text','experience_summary','preferred_channel_id'
        ] as $f) {
            $this->$f = $student->$f;
        }

        $this->timezones = $this->buildTimezoneOptions();

        $this->selectedTags = $student->tags()
            ->select('tags.id','tags.name','tags.code','tags.color')
            ->orderBy('tags.name')->get()->map->toArray()->all();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['nullable','string','max:100'],
            'last_name'  => ['nullable','string','max:100'],
            'document_number' => ['nullable','string','max:100'],
            'email'      => ['nullable','email','max:190'],
            'phone'      => ['nullable','string','max:100'],
            'status'     => ['required', Rule::in(['active','paused','inactive','prospect'])],
            'timezone'   => ['nullable','string','max:64'],
            'birth_date' => ['nullable','date'],
            'gender'     => ['nullable', Rule::in(['male','female','non_binary','other'])],
            'language'   => ['nullable', Rule::in(['es','en'])],
            'is_user_enabled' => ['boolean'],
            'avatar'     => ['nullable', \Illuminate\Validation\Rules\File::image()->max(4096)],
            'primary_training_goal_id' => ['nullable','exists:training_goals,id'],
            'height_cm'  => ['nullable','numeric','between:0,500'],
            'weight_kg'  => ['nullable','numeric','between:0,500'],
            'availability_text' => ['nullable','string','max:2000'],
            'experience_summary' => ['nullable','string','max:2000'],
            'preferred_channel_id' => ['nullable','exists:communication_channels,id'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $this->student->fill($data)->save();

        if ($this->avatar) {
            $this->student->clearMediaCollection('avatar');
            $this->student->addMedia($this->avatar->getRealPath())
                ->usingFileName($this->avatar->getClientOriginalName())
                ->toMediaCollection('avatar');
        }

        $ids = collect($this->selectedTags)->pluck('id')->all();
        $this->student->tags()->sync($ids);

        session()->flash('success', __('site.student_updated'));
        $this->dispatch('updated');
        $this->mount($this->student->fresh());
    }

    public function render()
    {
        /** @var \Illuminate\View\View $view */
        $view = view('livewire.tenant.students.profile.form', [
            'goals'    => TrainingGoal::orderBy('name')->get(['id','name']),
            'channels' => CommunicationChannel::orderBy('name')->get(['id','name']),
        ]);

        $aptInDays = optional($this->student->apt_fitness_expires_at)?->diffInDays(now(), false);

        return $view->layoutData([
            'student'          => $this->student,
            'active'           => 'profile',
            'overdueInvoices'  => 0,
            'aptExpiresInDays' => ($aptInDays!==null && $aptInDays>=0)?$aptInDays:null,
            'unreadMessages'   => 0,
        ]);
    }
}
