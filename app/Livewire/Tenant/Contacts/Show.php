<?php

namespace App\Livewire\Tenant\Contacts;

use Livewire\Component;
use App\Models\Contact;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

#[Layout('components.layouts.tenant')]
class Show extends Component
{
    public Contact $contact;

    public function mount(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function render()
    {
        return view('livewire.tenant.contacts.show');
    }
}
