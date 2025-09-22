<?php

namespace App\Livewire\Tenant\Students\Files;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Tenant\Student;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant.students.settings')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.tenant.students.files.index');
    }
}
