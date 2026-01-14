<?php

namespace App\Livewire\Tenant\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use App\Models\Tenant\{Payment, Student};
use App\Services\Tenant\Payments\PaymentService;
use Illuminate\Support\Arr;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public ?int $student_id = null;
    public float $amount = 0;
    public string $notes = '';
    public bool $back = true;
    public bool $autoAmount = true;

    public function mount($payment = null, $student = null): void
    {
        // Si viene desde un alumno
        if ($student && !$payment) {
            $found = Student::where('uuid', $student)->first();
            if ($found) {
                $this->student_id = $found->id;
            }
        }

        // Si es edición
        if ($payment) {
            $foundPayment = Payment::where('uuid', $payment)->first();
            if ($foundPayment) {
                $this->editMode = true;
                $this->id = $foundPayment->id;
                $this->student_id = $foundPayment->student_id;
                $this->amount = $foundPayment->amount;
                $this->notes = $foundPayment->notes ?? '';
                $this->autoAmount = false;
            }
        }

        if ($this->student_id && !$payment) {
            $this->prefillAmountFromPlan();
        }
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'amount'     => ['required', 'numeric', 'min:1'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save(PaymentService $service)
    {
        $validated = $this->validate();

        if ($this->editMode) {
            $payment = Payment::findOrFail($this->id);
            $payment->fill($validated)->save();

            $this->dispatch('updated');
            session()->flash('success', __('site.payment_updated'));
        } else {
            $student = Student::findOrFail($this->student_id);
            if ($this->autoAmount) {
                $planAmount = $this->resolvePlanAmount($student);
                if ($planAmount) {
                    $validated['amount'] = $planAmount;
                }
            }
            $service->createForStudent(
                $student,
                $validated['amount'],
                $this->notes
            );

            session()->flash('success', __('site.payment_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.payments.index'), navigate: true);
        }

        if ($this->editMode) {
            $this->mount($this->id);
        }
    }

    public function updatedStudentId(): void
    {
        $this->prefillAmountFromPlan();
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

        $billingFrequency = $student->billing_frequency ?? 'monthly';
        $selected = $pricing->firstWhere('type', $billingFrequency) ?? $pricing->first();

        return Arr::get($selected, 'amount') ? (float) Arr::get($selected, 'amount') : null;
    }

    public function render()
    {
        return view('livewire.tenant.payments.form', [
            'students' => Student::orderBy('first_name')->get(),
        ]);
    }
}
