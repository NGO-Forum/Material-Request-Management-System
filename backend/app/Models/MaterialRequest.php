<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'requester_id',
        'material_id',
        'quantity',
        'receipt_date',
        'purpose',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'receipt_date' => 'date:Y-m-d',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        // 'status' is string, no need to cast
    ];

    // Relationships
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
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