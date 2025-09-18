<?php

namespace App\Livewire\Central;

use App\Jobs\Central\SendContactFormMail;
use Livewire\Component;
use App\Models\Central\Contact;
use Illuminate\Support\Str;


class ContactForm extends Component
{

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $body = '';
    public string $formKey;

    public function mount(): void
    {
        $this->formKey = (string) Str::uuid();
    }


    protected $rules = [
        'name'  => 'required|string|min:2|max:150',
        'email' => 'required|email|max:190',
        'phone' => 'nullable|string|max:50',
        'body'  => 'required|string|min:5',
    ];

    protected $messages = [
        'name.required'  => 'El nombre es obligatorio.',
        'email.required' => 'El email es obligatorio.',
        'email.email'    => 'Debe ser un email válido.',
        'body.required'  => 'La consulta es obligatoria.',
    ];


    public function save(): void
    {
        $this->validate();

        $contact = Contact::create([
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'message'    => $this->body,
            'unread'     => true,
        ]);

        $this->reset(['name', 'email', 'phone', 'body']);
        $this->resetValidation();

        $this->formKey = (string) Str::uuid();
        SendContactFormMail::dispatch(
            $contact->name,
            $contact->email,
            $contact->phone,
            $contact->message,
        );

        $this->dispatch('contact-show-saved');
    }
    public function render()
    {
        return view('livewire.central.contact-form');
    }
}
