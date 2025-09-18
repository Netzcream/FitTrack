<?php

namespace App\Livewire\Tenant\CommercialPlans;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Arr;
use App\Models\Tenant\CommercialPlan;
use App\Http\Requests\Tenant\CommercialPlan\StoreCommercialPlanRequest;
use App\Http\Requests\Tenant\CommercialPlan\UpdateCommercialPlanRequest;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    // basics
    public string $name = '';
    public string $code = '';
    public ?string $slug = null;
    public ?string $description = null;

    // pricing
    public ?float $monthly_price = null;
    public ?float $yearly_price = null;
    public string $currency = 'USD';
    public string $billing_interval = 'both';
    public int $trial_days = 0;

    // limits (direct fields)
    public ?int $max_users = null;
    public ?int $max_teams = null;
    public ?int $max_projects = null;
    public ?int $storage_gb = null;

    // flags
    public bool $is_active = true;
    public string $visibility = 'public';
    public string $plan_type = 'standard';
    public int $sort_order = 0;

    // external ids
    public ?string $external_product_id = null;
    public ?string $external_monthly_price_id = null;
    public ?string $external_yearly_price_id = null;

    // ===== Collections UI (no JSON textarea) =====
    // Features as key/value (strings)
    public string $featureKey = '';
    public string $featureValue = '';
    /** @var array<int, array{key:string,value:string}> */
    public array $featuresList = [];

    // Additional limits as key/value (numeric)
    public string $limitKey = '';
    public ?int $limitValue = null;
    /** @var array<int, array{key:string,value:int}> */
    public array $limitsList = [];

    public bool $back = true;

    public function mount(?CommercialPlan $commercialPlan): void
    {
        $this->featuresList = [];
        $this->limitsList   = [];
        $this->featureKey   = '';
        $this->featureValue = '';
        $this->limitKey     = '';
        $this->limitValue   = null;


        if ($commercialPlan && $commercialPlan->exists) {
            $this->editMode = true;
            $this->id = (int) $commercialPlan->id;


            foreach (
                [
                    'name',
                    'code',
                    'slug',
                    'description',
                    'monthly_price',
                    'yearly_price',
                    'currency',
                    'billing_interval',
                    'trial_days',
                    'max_users',
                    'max_teams',
                    'max_projects',
                    'storage_gb',
                    'is_active',
                    'visibility',
                    'plan_type',
                    'sort_order',
                    'external_product_id',
                    'external_monthly_price_id',
                    'external_yearly_price_id',
                ] as $attr
            ) {
                $this->{$attr} = $commercialPlan->{$attr};
            }

            // Normalize 'features' (array) into featuresList [ [key,value], ... ]
            $features = $commercialPlan->features ?? [];
            if (Arr::isAssoc($features)) {
                foreach ($features as $k => $v) {
                    $this->featuresList[] = ['key' => (string)$k, 'value' => is_scalar($v) ? (string)$v : json_encode($v)];
                }
            } else {
                // if it came as list of pairs or list of strings "label"
                foreach ($features as $item) {
                    if (is_array($item) && array_key_exists('key', $item) && array_key_exists('value', $item)) {
                        $this->featuresList[] = ['key' => (string)$item['key'], 'value' => (string)$item['value']];
                    } elseif (is_string($item)) {
                        $this->featuresList[] = ['key' => $item, 'value' => 'true'];
                    }
                }
            }

            // Normalize 'limits' (array) into limitsList [ [key,value:int], ... ]
            $limits = $commercialPlan->limits ?? [];
            if (Arr::isAssoc($limits)) {
                foreach ($limits as $k => $v) {
                    $this->limitsList[] = ['key' => (string)$k, 'value' => (int)$v];
                }
            } else {
                foreach ($limits as $item) {
                    if (is_array($item) && array_key_exists('key', $item) && array_key_exists('value', $item)) {
                        $this->limitsList[] = ['key' => (string)$item['key'], 'value' => (int)$item['value']];
                    }
                }
            }
        }
    }

    public function save()
    {
        $store  = new StoreCommercialPlanRequest();
        $update = new UpdateCommercialPlanRequest();

        $rules = $this->editMode ? $update->rules($this->id) : $store->rules();

        if ($this->editMode) {
            request()->merge(['id' => $this->id]);
        }

        // Estas reglas apuntan a props que NO existen en el componente (features/limits)
        unset($rules['features'], $rules['limits']);

        // castear strings vacíos a null en numéricos
        foreach (['monthly_price', 'yearly_price', 'max_users', 'max_teams', 'max_projects', 'storage_gb'] as $k) {
            if ($this->{$k} === '') $this->{$k} = null;
        }

        // Validar solo lo que el componente realmente expone
        $baseValidated = $this->validate($rules);

        // Construir arrays para columnas casteadas (sin textareas JSON)
        $features = [];
        foreach ($this->featuresList as $row) {
            $k = trim($row['key']);
            if ($k === '') continue;
            $features[$k] = (string) ($row['value'] ?? '');
        }

        $limits = [];
        foreach ($this->limitsList as $row) {
            $k = trim($row['key']);
            if ($k === '') continue;
            $limits[$k] = max(0, (int) ($row['value'] ?? 0));
        }

        // Merge final que se envía al modelo
        $validated = array_merge($baseValidated, [
            'features' => $features,
            'limits'   => $limits,
        ]);

        $plan = $this->editMode
            ? \App\Models\Tenant\CommercialPlan::findOrFail($this->id)
            : new \App\Models\Tenant\CommercialPlan();

        $plan->fill($validated);
        $plan->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('Plan updated'));
            $this->mount($plan->fresh());
        } else {
            session()->flash('success', __('Plan created'));
        }




        if ($this->back) {
            return $this->redirect(
                route('tenant.dashboard.commercial-plans.index'),
                navigate: true
            );
        }



        return $this->redirect(
            route('tenant.dashboard.commercial-plans.edit', $plan),
            navigate: true
        );
    }


    // ============ Features handlers ============
    public function addFeature(): void
    {
        $k = trim($this->featureKey);
        $v = trim($this->featureValue);

        if ($k === '') return;

        // replace if key exists
        foreach ($this->featuresList as &$row) {
            if ($row['key'] === $k) {
                $row['value'] = $v === '' ? 'true' : $v;
                $this->featureKey = '';
                $this->featureValue = '';
                return;
            }
        }
        $this->featuresList[] = ['key' => $k, 'value' => $v === '' ? 'true' : $v];
        $this->featureKey = '';
        $this->featureValue = '';
    }

    public function removeFeature(int $index): void
    {
        unset($this->featuresList[$index]);
        $this->featuresList = array_values($this->featuresList);
    }

    // ============ Limits handlers ============
    public function addLimit(): void
    {
        $k = trim($this->limitKey);
        if ($k === '' || $this->limitValue === null || $this->limitValue < 0) return;

        // replace if key exists
        foreach ($this->limitsList as &$row) {
            if ($row['key'] === $k) {
                $row['value'] = (int)$this->limitValue;
                $this->limitKey = '';
                $this->limitValue = null;
                return;
            }
        }
        $this->limitsList[] = ['key' => $k, 'value' => (int)$this->limitValue];
        $this->limitKey = '';
        $this->limitValue = null;
    }

    public function removeLimit(int $index): void
    {
        unset($this->limitsList[$index]);
        $this->limitsList = array_values($this->limitsList);
    }

    public function render()
    {
        return view('livewire.tenant.commercial-plans.form');
    }
}
