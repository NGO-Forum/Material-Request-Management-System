<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'returned_by',
        'return_date',
        'it_inspected_by',
        'it_condition_status',
        'it_remarks',
        'final_confirmed_by',
        'admin_remarks',
    ];

    public function request()
    {
        return $this->belongsTo(MaterialRequest::class, 'request_id');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function itInspector()
    {
        return $this->belongsTo(User::class, 'it_inspected_by');
    }

    public function finalInspector()
    {
        return $this->belongsTo(User::class, 'final_confirmed_by');
    }
}
