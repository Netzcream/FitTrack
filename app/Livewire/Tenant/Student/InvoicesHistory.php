<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Tenant\Student;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.student')]
class InvoicesHistory extends Component
{
    use WithPagination;

    public ?Student $student = null;
    public int $perPage = 10;

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $this->student = Student::where('email', $user->email)->firstOrFail();
    }

    public function render()
    {
        $invoices = $this->student->invoices()
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.tenant.student.invoices-history', [
            'invoices' => $invoices,
            'student' => $this->student,
        ]);
    }
}
