<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialStockMovement extends Model
{
    use HasFactory;

    public $timestamps = false; // created_at handled automatically

    protected $fillable = [
        'material_id',
        'request_id',
        'movement_type',
        'quantity',
        'remarks',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function request()
    {
        return $this->belongsTo(MaterialRequest::class, 'request_id');
    }
}
