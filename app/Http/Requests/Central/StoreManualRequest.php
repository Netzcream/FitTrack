<?php

namespace App\Http\Requests\Central;

use App\Enums\ManualCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:manuals,slug'],
            'category' => ['required', Rule::enum(ManualCategory::class)],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_active' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => __('manuals.title'),
            'category' => __('manuals.category'),
            'summary' => __('manuals.summary'),
            'content' => __('manuals.content'),
            'is_active' => __('manuals.is_active'),
            'published_at' => __('manuals.published_at'),
            'sort_order' => __('manuals.sort_order'),
        ];
    }
}
