<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestStatus extends Model
{
    use HasFactory;

    const PENDING_EMPLOYEE_SIGNATURE = 'pending_employee_signature';
    const PENDING_APPROVAL = 'pending_approval';
    const APPROVED = 'approved';
    const EXPORTED_TO_SAP = 'exported_to_sap';
    const REJECTED = 'rejected';
    const PENDING_ADMIN_APPROVAL = 'pending_admin_approval';
    

    protected $table = 'request_statuses';

    protected $fillable = [
        'code',
        'name',
    ];

    public function requests(): HasMany
    {
        return $this->hasMany(HrRequest::class, 'status_id');
    }
}