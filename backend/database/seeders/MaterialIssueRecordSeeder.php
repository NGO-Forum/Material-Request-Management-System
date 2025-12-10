<?php
namespace Database\Seeders;

use App\Models\MaterialIssueRecord;
use App\Models\MaterialRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialIssueRecordSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role_id', 1)->first();
        $requests = MaterialRequest::whereIn('status', ['admin_approved', 'issued'])->get();

        foreach ($requests as $req) {
            if (rand(0,1)) {
                MaterialIssueRecord::create([
                    'request_id' => $req->id,
                    'issued_by' => $admin->id,
                    'issued_date' => now()->subDays(rand(1,7)),
                    'expected_return_date' => now()->addDays(14),
                ]);
            }
        }
    }
}