<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Student;
use App\Services\Tenant\Payments\MercadoPagoService;

class PaymentController extends Controller
{
    public function create(Request $request, MercadoPagoService $mp)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount'     => 'required|numeric|min:0.1',
        ]);

        $student = Student::findOrFail($data['student_id']);

        $payment = Payment::create([
            'student_id' => $student->id,
            'amount'     => $data['amount'],
            'method'     => 'mercadopago',
            'status'     => 'pending',
        ]);

        $url = $mp->createPaymentLink($payment);

        return response()->json(['url' => $url]);
    }
}
