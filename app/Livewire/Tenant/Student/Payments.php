<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.student')]
class Payments extends Component
{
    public function render()
    {
        $acceptedMethods = accepted_payment_methods();
        $transferConfig = payment_method_config('transfer');
        $mercadopagoConfig = payment_method_config('mercadopago');
        $cashConfig = payment_method_config('cash');

        /** @var User $user */
        $user = auth()->user();
        $student = $user->student;

        return view('livewire.tenant.student.payments', [
            'acceptedMethods' => $acceptedMethods,
            'transferConfig' => $transferConfig,
            'mercadopagoConfig' => $mercadopagoConfig,
            'cashConfig' => $cashConfig,
            'student' => $student,
        ]);
    }
}
