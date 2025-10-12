<?php

namespace App\Livewire\Tenant\CommercialPlans;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\CommercialPlan;
use Illuminate\Validation\Rule;
use App\Enums\CommercialPlan\PricingType;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?CommercialPlan $plan = null;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public array $pricing = [];   // [['type'=>'monthly','amount'=>8000,'currency'=>'ARS','label'=>'ARS 8.000 / mes']]
    public array $features = [];
    public array $limits = [];

    public bool $editMode = false;
    public bool $back = false;

    public function mount(?CommercialPlan $commercialPlan): void
    {
        if ($commercialPlan && $commercialPlan->exists) {
            $this->plan = $commercialPlan;
            $this->editMode = true;

            $this->name        = $commercialPlan->name;
            $this->description = $commercialPlan->description ?? '';
            $this->is_active   = (bool) $commercialPlan->is_active;

            $this->pricing  = array_values($commercialPlan->pricing ?? []);
            $this->features = array_values($commercialPlan->features ?? []);
            $this->limits   = $commercialPlan->limits ?? [];
        } else {
            $this->pricing = [[
                'type' => 'monthly',
                'amount' => '',
                'currency' => 'ARS',
                'label' => '',
            ]];
            $this->features = [''];
            $this->limits = [
                'sessions_per_week' => '',
                'video_calls' => '',
                'in_person' => false,
            ];
        }
    }

    protected function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', Rule::unique('commercial_plans', 'name')->ignore($this->plan?->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],

            'pricing'     => ['array'],
            'pricing.*.type'     => ['required', Rule::in(PricingType::values())],
            'pricing.*.amount'   => ['required', 'numeric', 'min:0'],
            'pricing.*.currency' => ['required', 'string', 'max:10'],
            'pricing.*.label'    => ['nullable', 'string', 'max:255'],

            'features'    => ['array'],
            'limits'      => ['array'],
        ];
    }

    public function getPricingTypeOptionsProperty(): array
    {
        return PricingType::options();
    }

    public function addPrice(): void
    {
        $this->pricing[] = [
            'type' => 'monthly',
            'amount' => '',
            'currency' => 'ARS',
            'label' => '',
        ];
    }

    public function removePrice(int $index): void
    {
        unset($this->pricing[$index]);
        $this->pricing = array_values($this->pricing);
    }

    public function addFeature(): void
    {
        $this->features[] = '';
    }

    public function removeFeature(int $index): void
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function save(): void
    {
        $validated = $this->validate();
        $plan = $this->editMode ? $this->plan : new CommercialPlan();

        if (!$this->editMode) {
            $plan->order = (CommercialPlan::max('order') ?? 0) + 1;
        }

        $plan->fill([
            'name'        => $this->name,
            'description' => $this->description,
            'is_active'   => $this->is_active,
            'pricing'     => array_values($this->pricing),
            'features'    => array_filter($this->features),
            'limits'      => $this->limits,
        ]);

        $plan->save();

        $this->dispatch('saved');

        if ($this->back) {
            redirect()->route('tenant.dashboard.commercial-plans.index');
        } else {
            session()->flash('success', $this->editMode ? __('common.updated') : __('common.created'));
        }
    }

    public function render()
    {
        return view('livewire.tenant.commercial-plans.form');
    }
}
