<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id', 'name', 'model', 'serial_number',
        'qty_stock', 'qty_issued', 'qty_remaining',
        'location', 'condition', 'image', 'remarks'
    ];

    // Each Material belongs to a Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
