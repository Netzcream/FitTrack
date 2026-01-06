<?php

namespace App\Livewire\Tenant\Payments;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\{Payment, Student, PaymentMethod};

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public string $search = '';
    public string $status = '';
    public int $perPage = 10;
    public ?int $paymentToMark = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updating($field): void
    {
        if (in_array($field, ['search', 'status'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function confirmMarkAsPaid(int $id): void
    {
        $this->paymentToMark = $id;
    }

    public function markAsPaid(): void
    {
        $payment = Payment::find($this->paymentToMark);
        if (!$payment) return;

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->dispatch('payment-marked');
        $this->dispatch('toast', type: 'success', message: __('payments.marked_paid_success'));
        $this->reset('paymentToMark');
    }

    public function render()
    {
        $query = Payment::query()
            ->with(['student', 'paymentMethod'])
            // Búsqueda agrupada correctamente
            ->when($this->search, function ($q) {
                $t = "%{$this->search}%";
                $q->whereHas('student', function ($s) use ($t) {
                    $s->where(function ($sq) use ($t) {
                        $sq->where('first_name', 'like', $t)
                           ->orWhere('last_name', 'like', $t)
                           ->orWhere('email', 'like', $t);
                    });
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.tenant.payments.index', [
            'payments' => $query->paginate($this->perPage),
        ]);
    }
}
