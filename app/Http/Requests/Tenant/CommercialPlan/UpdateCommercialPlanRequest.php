<?php

namespace App\Http\Requests\Tenant\CommercialPlan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommercialPlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    // Úsalo desde Livewire: UpdateCommercialPlanRequest::rulesFor($id)
    public static function rulesFor(?int $id): array
    {

        return [
            'name'   => ['sometimes','string','max:255'],
            'code'   => ['sometimes','string','max:64', Rule::unique('commercial_plans','code')->ignore($id, 'id')],
            'slug'   => ['nullable','string','max:255', Rule::unique('commercial_plans','slug')->ignore($id, 'id')],
            'description' => ['nullable','string'],

            'monthly_price' => ['nullable','numeric','min:0'],
            'yearly_price'  => ['nullable','numeric','min:0'],
            'currency'      => ['sometimes','string','size:3'],

            'billing_interval' => ['sometimes','in:monthly,yearly,both'],
            'trial_days'       => ['nullable','integer','min:0','max:60'],

            'max_users'    => ['nullable','integer','min:1'],
            'max_teams'    => ['nullable','integer','min:1'],
            'max_projects' => ['nullable','integer','min:1'],
            'storage_gb'   => ['nullable','integer','min:1'],

            'is_active'  => ['boolean'],
            'visibility' => ['sometimes','in:public,private'],
            'plan_type'  => ['sometimes','in:free,standard,pro,enterprise'],

            'features' => ['nullable','array'],
            'limits'   => ['nullable','array'],

            'external_product_id'        => ['nullable','string','max:191'],
            'external_monthly_price_id'  => ['nullable','string','max:191'],
            'external_yearly_price_id'   => ['nullable','string','max:191'],

            'sort_order' => ['nullable','integer','min:0'],
        ];
    }

    // Sigue siendo usable en rutas/controller
    public function rules($id): array
    {


        $id = $id ? (int) $id : null;

        return self::rulesFor($id);
    }
}
