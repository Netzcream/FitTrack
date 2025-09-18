<?php

namespace App\Http\Requests\Tenant\CommercialPlan;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommercialPlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'   => ['required','string','max:255'],
            'code'   => ['required','string','max:64','unique:commercial_plans,code'],
            'slug'   => ['nullable','string','max:255','unique:commercial_plans,slug'],
            'description' => ['nullable','string'],

            'monthly_price' => ['nullable','numeric','min:0'],
            'yearly_price'  => ['nullable','numeric','min:0'],
            'currency'      => ['required','string','size:3'],

            'billing_interval' => ['required','in:monthly,yearly,both'],
            'trial_days'       => ['nullable','integer','min:0','max:60'],

            'max_users'    => ['nullable','integer','min:1'],
            'max_teams'    => ['nullable','integer','min:1'],
            'max_projects' => ['nullable','integer','min:1'],
            'storage_gb'   => ['nullable','integer','min:1'],

            'is_active'  => ['boolean'],
            'visibility' => ['required','in:public,private'],
            'plan_type'  => ['required','in:free,standard,pro,enterprise'],

            'features' => ['nullable','array'],
            'limits'   => ['nullable','array'],

            'external_product_id'        => ['nullable','string','max:191'],
            'external_monthly_price_id'  => ['nullable','string','max:191'],
            'external_yearly_price_id'   => ['nullable','string','max:191'],

            'sort_order' => ['nullable','integer','min:0'],
        ];
    }
}
