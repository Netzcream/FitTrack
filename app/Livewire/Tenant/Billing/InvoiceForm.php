<?php

namespace App\Livewire\Tenant\Billing;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Tenant\Student;
use App\Services\Tenant\InvoiceService;
use Illuminate\Support\Arr;

#[Layout('components.layouts.tenant')]
class InvoiceForm extends Component
{
    public ?int $student_id = null;
    public float $amount = 0;
    public ?string $due_date = null; // Y-m-d
    public string $label = '';
    public string $notes = '';
    public bool $autoAmount = true;
    public bool $back = true;

    public function mount(?string $student = null): void
    {
        if ($student) {
            $found = Student::where('uuid', $student)->first();
            if ($found) {
                $this->student_id = $found->id;
                $this->prefillAmountFromPlan();
            }
        }
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'label' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function updatedStudentId(): void
    {
        $this->resetErrorBag('amount');
        $this->prefillAmountFromPlan();
    }

    public function updatedAutoAmount(): void
    {
        $this->resetErrorBag('amount');

        if ($this->autoAmount) {
            $this->prefillAmountFromPlan();

            if (!$this->autoAmount) {
                $this->addError('amount', __('invoices.no_plan_amount'));
            }
        }
    }

    public function save(InvoiceService $invoiceService)
    {
        if (!$this->student_id) {
            $this->validate();
            return;
        }

        $student = Student::findOrFail($this->student_id);

        if ($this->autoAmount) {
            $planAmount = $this->resolvePlanAmount($student);
            if ($planAmount) {
                $this->amount = $planAmount;
            } else {
                $this->autoAmount = false;
                $this->addError('amount', __('invoices.no_plan_amount'));
                return;
            }
        }

        $data = $this->validate();

        $meta = array_filter([
            'label' => $data['label'] ?? null,
            'notes' => $data['notes'] ?? null,
            'source' => 'manual',
        ], fn($value) => $value !== null && $value !== '');

        // Solo asociar al plan si se está usando el importe del plan
        $planAssignment = $this->autoAmount ? $student->currentPlanAssignment : null;

        $invoiceService->createForStudent(
            $student,
            $planAssignment,
            $this->due_date ? now()->parse($this->due_date) : null,
            $data['amount'],
            $meta
        );

        session()->flash('success', __('invoices.created_success'));

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.billing.invoices.index'), navigate: true);
        }
    }

    private function prefillAmountFromPlan(): void
    {
        $student = Student::find($this->student_id);
        $planAmount = $student ? $this->resolvePlanAmount($student) : null;
        if ($planAmount) {
            $this->amount = $planAmount;
            $this->autoAmount = true;
        } else {
            $this->autoAmount = false;
        }
    }

    private function resolvePlanAmount(Student $student): ?float
    {
        $plan = $student->commercialPlan;
        if (!$plan) {
            return null;
        }
        $pricing = collect($plan->pricing ?? []);
        if ($pricing->isEmpty()) {
            return null;
        }
        $billing = $student->billing_frequency ?? 'monthly';
        $selected = $pricing->firstWhere('type', $billing) ?? $pricing->first();
        return Arr::get($selected, 'amount') ? (float) Arr::get($selected, 'amount') : null;
    }

    public function render()
    {
        return view('livewire.tenant.billing.invoice-form', [
            'students' => Student::orderBy('first_name')->get(['id', 'first_name', 'last_name']),
        ]);
    }
}

