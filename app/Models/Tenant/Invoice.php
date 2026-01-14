<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $table = 'invoices';

    protected $fillable = [
        'uuid',
        'student_id',
        'plan_assignment_id',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'external_reference',
        'meta',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'meta' => 'array',
        'amount' => 'decimal:2',
    ];

    /* ------------------------ Relaciones ------------------------ */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function planAssignment(): BelongsTo
    {
        return $this->belongsTo(StudentPlanAssignment::class, 'plan_assignment_id');
    }

    /* ------------------------ Scopes ------------------------ */

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'overdue']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    /* ------------------------ Accessors ------------------------ */

    public function getIsPendingAttribute(): bool
    {
        return in_array($this->status, ['pending', 'overdue']);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'overdue';
    }

    public function getFormattedAmountAttribute(): string
    {
        $currency = $this->meta['currency'] ?? 'ARS';
        return "{$currency} " . number_format((float) $this->amount, 2, ',', '.');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
