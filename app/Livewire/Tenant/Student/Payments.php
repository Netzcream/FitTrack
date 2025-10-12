<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.student')]
class Payments extends Component
{
    public function render()
    {
        return view('livewire.tenant.student.payments');
    }
}
