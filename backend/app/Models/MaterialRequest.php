<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
    'requester_id',
    'material_id',
    'quantity',
    'receipt_date',
    'purpose',
    'status',
    'manager_id',
    'admin_hr_id',
    'it_staff_id',
    'remarks'
];

protected $casts = [
    'receipt_date' => 'date:Y-m-d',
    'created_at'   => 'datetime',
    'updated_at'   => 'datetime',
];

    // Relationships
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function adminHR()
    {
        return $this->belongsTo(User::class, 'admin_hr_id');
    }

    public function itStaff()
    {
        return $this->belongsTo(User::class, 'it_staff_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function actions()
    {
        return $this->hasMany(MaterialRequestAction::class, 'request_id');
    }

    public function issueRecord()
    {
        return $this->hasOne(MaterialIssueRecord::class, 'request_id');
    }

    public function returnRecord()
    {
        return $this->hasOne(MaterialReturn::class, 'request_id');
    }
}
