<?php
namespace Database\Seeders;

use App\Models\MaterialReturn;
use App\Models\MaterialRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialReturnSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role_id', 1)->first();
        $requests = MaterialRequest::where('status', 'issued')->orWhere('status', 'returned')->get();

        foreach ($requests as $req) {
            if ($req->status === 'returned' || rand(0,1)) {
                MaterialReturn::create([
                    'request_id' => $req->id,
                    'returned_by' => $req->requester_id,
                    'return_date' => now()->subDays(rand(1,5)),
                    'it_inspected_by' => $admin->id,
                    'it_condition_status' => 'Good',
                    'it_remarks' => 'Returned in good condition',
                    'final_confirmed_by' => $admin->id,
                    'admin_remarks' => 'Stock updated',
                ]);
            }
        }
    }
}