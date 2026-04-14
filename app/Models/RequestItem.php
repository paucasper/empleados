<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestItem extends Model
{
    use HasFactory;

    protected $table = 'request_items';

    protected $fillable = [
        'request_id',
        'expense_type',
        'expense_date',
        'description',
        'quantity',
        'unit_amount',
        'amount',
        'is_card_payment',
        'ticket_path',
        'ticket_original_name',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'is_card_payment' => 'boolean',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrRequest::class, 'request_id');
    }
}