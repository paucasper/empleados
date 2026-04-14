<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'signer_user_id',
        'sap_employee_id',
        'awart',
        'begda',
        'endda',
        'description',
        'comment',
        'location',
        'phone',
        'status',
        'employee_signed_at',
        'signer_signed_at',
        'rejected_at',
        'rejection_reason',
        'sap_file_name',
        'sap_exported_at',
    ];

    protected $casts = [
        'begda' => 'date',
        'endda' => 'date',
        'employee_signed_at' => 'datetime',
        'signer_signed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'sap_exported_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_user_id');
    }
}