<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\Payment;

#[Layout('layouts.student')]
class Payments extends Component
{
    use WithFileUploads;

    public $pendingPayment;
    public $proof;
    public $uploadSuccess = false;

    public function mount()
    {
        $student = Auth::user()?->student;
        if (!$student) abort(403, 'Acceso no autorizado');

        $this->pendingPayment = Payment::query()
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->latest('created_at')
            ->first();
    }

    public function uploadProof()
    {
        $student = Auth::user()?->student;
        if (!$student || !$this->pendingPayment) return;

        $this->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Limpiar media anterior
        $this->pendingPayment->clearMediaCollection('proofs');

        // Agregar nuevo comprobante
        $this->pendingPayment
            ->addMedia($this->proof->getRealPath())
            ->usingFileName('proof_' . now()->timestamp . '.' . $this->proof->getClientOriginalExtension())
            ->toMediaCollection('proofs');

        $this->pendingPayment->update(['status' => 'under_review']);

        $this->uploadSuccess = true;
        $this->dispatch('toast', type: 'success', message: 'Comprobante subido correctamente.');
    }

    public function render()
    {
        return view('livewire.tenant.student.payments');
    }
}
