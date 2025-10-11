<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\Payment;

class PaymentsHistory extends Component
{
    use WithPagination;

    public function render()
    {
        $student = Auth::user()?->student;
        $payments = Payment::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->paginate(6);

        return view('livewire.tenant.student.payments-history', compact('payments'));
    }
}
