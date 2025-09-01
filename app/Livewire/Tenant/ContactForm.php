<?php

namespace App\Livewire\Tenant;

use Livewire\Component;
use App\Models\Contact;
use App\Jobs\Tenant\SendContactFormMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ContactForm extends Component
{

    public $name;
    public $email;
    public $mobile;
    public $message;


    public function submit()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'mobile' => 'required|min:8',
            'message' => 'required|min:5',
        ]);


        Log::info('Tenant ID in submit: ' . (tenant('id') ?? 'none'));

        Contact::create([
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'message' => $this->message,
        ]);
        SendContactFormMail::dispatch(
            $this->name,
            $this->email,
            $this->mobile,
            $this->message,
        );

        $this->reset();

        session()->flash('success', '¡Formulario enviado exitosamente! ');

        $this->dispatch('success-sent');
    }


    public function render()
    {
        return view('livewire.tenant.contact-form');
    }
}
