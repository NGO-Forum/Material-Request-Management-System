<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequestAction extends Model
{
    use HasFactory;

    public $timestamps = false; // because created_at already stored manually

    protected $fillable = [
        'request_id',
        'action_by',
        'action_type',
        'remarks',
    ];

    public function request()
    {
        return $this->belongsTo(MaterialRequest::class, 'request_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
