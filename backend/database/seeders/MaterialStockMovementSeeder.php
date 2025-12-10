<?php
namespace Database\Seeders;

use App\Models\MaterialStockMovement;
use App\Models\MaterialRequest;
use Illuminate\Database\Seeder;

class MaterialStockMovementSeeder extends Seeder
{
    public function run(): void
    {
        MaterialRequest::where('status', 'issued')->each(function ($req) {
            MaterialStockMovement::create([
                'material_id' => $req->material_id,
                'request_id' => $req->id,
                'movement_type' => 'issue',
                'quantity' => $req->quantity,
                'remarks' => 'Issued to ' . $req->requester->name,
            ]);
        });

        MaterialRequest::where('status', 'returned')->each(function ($req) {
            MaterialStockMovement::create([
                'material_id' => $req->material_id,
                'request_id' => $req->id,
                'movement_type' => 'return',
                'quantity' => $req->quantity,
                'remarks' => 'Returned by ' . $req->requester->name,
            ]);
        });
    }
}