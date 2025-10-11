<?php

namespace App\Services\Tenant\Payments;

use App\Models\Tenant\{Payment, Student};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Crear un pago para un alumno
     */
    public function createForStudent(Student $student, float $amount, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($student, $amount, $notes) {
            return Payment::create([
                'student_id' => $student->id,
                'amount' => $amount,
                'status' => 'new', // estado inicial
                'due_date' => Carbon::today()->addDays(7), // ejemplo: 7 días de plazo
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Cambiar estado del pago
     */
    public function updateStatus(Payment $payment, string $status): Payment
    {
        $payment->update([
            'status' => $status,
            'paid_at' => in_array($status, ['paid']) ? now() : null,
        ]);
        return $payment;
    }
}
