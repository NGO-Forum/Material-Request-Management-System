<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialIssueRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'issued_by',
        'issued_date',
        'expected_return_date',
        'actual_return_date',
    ];

    public function request()
    {
        return $this->belongsTo(MaterialRequest::class, 'request_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
