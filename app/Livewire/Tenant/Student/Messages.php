<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.student')]
class Messages extends Component
{
    public function render()
    {
        return view('livewire.tenant.student.messages');
    }
}
