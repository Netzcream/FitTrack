<?php

namespace App\Livewire\Tenant\Billing;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Invoice;
use App\Services\Tenant\InvoiceService;

#[Layout('components.layouts.tenant')]
class InvoicesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $paymentMethod = '';
    public ?string $dueFrom = null;
    public ?string $dueTo = null;
    public int $perPage = 10;

    public ?int $manualPaymentInvoiceId = null;
    public string $manualPaymentMethod = '';
    public ?string $manualPaidAt = null;
    public string $manualPaymentNotes = '';
    public ?string $manualPaymentReference = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'paymentMethod' => ['except' => ''],
        'dueFrom' => ['except' => null],
        'dueTo' => ['except' => null],
        'page' => ['except' => 1],
    ];

    public function updating($field): void
    {
        if (in_array($field, ['search', 'status', 'paymentMethod', 'dueFrom', 'dueTo'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'paymentMethod', 'dueFrom', 'dueTo']);
        $this->resetPage();
    }

    public function prepareManualPayment(int $id): void
    {
        $invoice = Invoice::find($id);
        if (!$invoice || !$invoice->is_pending) {
            return;
        }

        $this->manualPaymentInvoiceId = $invoice->id;
        $this->manualPaymentMethod = $invoice->payment_method ?: $this->defaultPaymentMethod();
        $this->manualPaidAt = now()->format('Y-m-d\TH:i');
        $this->manualPaymentNotes = '';
        $this->manualPaymentReference = null;
    }

    public function recordManualPayment(InvoiceService $invoiceService): void
    {
        $this->manualPaymentMethod = trim($this->manualPaymentMethod);
        $this->manualPaymentReference = $this->manualPaymentReference !== null
            ? trim($this->manualPaymentReference)
            : null;

        $this->validate([
            'manualPaymentInvoiceId' => ['required', 'integer'],
            'manualPaymentMethod' => ['required', 'string', 'max:50'],
            'manualPaidAt' => ['nullable', 'date'],
            'manualPaymentNotes' => ['nullable', 'string', 'max:500'],
            'manualPaymentReference' => ['nullable', 'string', 'max:100'],
        ]);

        $invoice = Invoice::find($this->manualPaymentInvoiceId);
        if (!$invoice || !$invoice->is_pending) {
            return;
        }

        $paidAt = $this->manualPaidAt ? now()->parse($this->manualPaidAt) : null;

        $invoiceService->markAsPaid(
            $invoice,
            $this->manualPaymentMethod,
            $this->manualPaymentReference ?: null,
            $paidAt
        );

        if ($this->manualPaymentNotes !== '') {
            $meta = $invoice->meta ?? [];
            $meta['payment_notes'] = $this->manualPaymentNotes;
            $invoice->update(['meta' => $meta]);
        }

        $this->dispatch('toast', type: 'success', message: __('payments.manual_payment_success'));
        $this->dispatch('invoice-marked');
        $this->reset([
            'manualPaymentInvoiceId',
            'manualPaymentMethod',
            'manualPaidAt',
            'manualPaymentNotes',
            'manualPaymentReference',
        ]);

        if ($this->status === 'pending') {
            $this->status = '';
        }
    }

    public function markAsOverdue(InvoiceService $invoiceService, int $id): void
    {
        $invoice = Invoice::find($id);
        if (!$invoice || $invoice->status !== 'pending') {
            return;
        }

        $invoiceService->markAsOverdue($invoice);
        $this->dispatch('toast', type: 'success', message: __('payments.marked_overdue_success'));
    }

    public function cancelInvoice(InvoiceService $invoiceService, int $id): void
    {
        $invoice = Invoice::find($id);
        if (!$invoice || !$invoice->is_pending) {
            return;
        }

        $invoiceService->cancel($invoice);
        $this->dispatch('toast', type: 'success', message: __('payments.marked_cancelled_success'));
    }

    private function defaultPaymentMethod(): string
    {
        $methods = accepted_payment_methods();
        return $methods[0] ?? 'manual';
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->with(['student'])
            ->when($this->search, function ($query) {
                $term = "%{$this->search}%";
                $query->whereHas('student', function ($studentQuery) use ($term) {
                    $studentQuery->where(function ($sq) use ($term) {
                        $sq->where('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->paymentMethod, fn($q) => $q->where('payment_method', $this->paymentMethod))
            ->when($this->dueFrom, fn($q) => $q->whereDate('due_date', '>=', $this->dueFrom))
            ->when($this->dueTo, fn($q) => $q->whereDate('due_date', '<=', $this->dueTo))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $selectedInvoice = $this->manualPaymentInvoiceId
            ? Invoice::with('student')->find($this->manualPaymentInvoiceId)
            : null;

        return view('livewire.tenant.billing.invoices-index', [
            'invoices' => $invoices,
            'selectedInvoice' => $selectedInvoice,
            'acceptedMethods' => accepted_payment_methods(),
        ]);
    }
}

