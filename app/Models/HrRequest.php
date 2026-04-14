<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrRequest extends Model
{
    use HasFactory;

    public const TYPE_EXPENSE = 'expense';
    public const TYPE_VACATION = 'vacation';

    protected $table = 'requests';

    protected $fillable = [
        'type',
        'user_id',
        'sap_employee_id',
        'approver_user_id',
        'status_id',
        'title',
        'description',
        'employee_signature_at',
        'approver_signature_at',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'sap_file_path',
        'sap_sent_at',
        'sap_response',
        'admin_user_id',
    ];

    protected $casts = [
        'employee_signature_at' => 'datetime',
        'approver_signature_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'sap_sent_at' => 'datetime',
    ];

    protected $appends = [
        'total_amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequestItem::class, 'request_id');
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopeVacations($query)
    {
        return $query->where('type', self::TYPE_VACATION);
    }

    public function scopeForApprover($query, int $userId)
    {
        return $query->where('approver_user_id', $userId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithStatusCode($query, string $statusCode)
    {
        return $query->whereHas('status', function ($q) use ($statusCode) {
            $q->where('code', $statusCode);
        });
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->items()->sum('amount');
    }

    public function isDraft(): bool
    {
        return $this->status?->code === RequestStatus::DRAFT;
    }

    public function isPendingApproval(): bool
    {
        return $this->status?->code === RequestStatus::PENDING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->status?->code === RequestStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status?->code === RequestStatus::REJECTED;
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}