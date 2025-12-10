<?php
// database/seeders/MaterialRequestSeeder.php

namespace Database\Seeders;

use App\Models\MaterialRequest;
use App\Models\User;
use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialRequestSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role_id', 6)->get();
        $managers  = User::where('role_id', 2)->get();
        $admin     = User::where('role_id', 1)->first();

        $requests = [
            ['requester_id' => 29, 'manager_id' => 25, 'admin_hr_id' => 28, 'it_staff_id' => 28, 'material_id' => 1,  'quantity' => 1,  'purpose' => 'Field visit Battambang',     'status' => 'admin_approved'],
            ['requester_id' => 30, 'manager_id' => 26, 'admin_hr_id' => 28, 'it_staff_id' => null, 'material_id' => 10, 'quantity' => 2,  'purpose' => 'Mouse broken',            'status' => 'manager_approved'],
            ['requester_id' => 14, 'manager_id' => 27, 'admin_hr_id' => null, 'it_staff_id' => null, 'material_id' => 5,  'quantity' => 15, 'purpose' => 'Workshop documentation', 'status' => 'pending'],
            ['requester_id' => 29, 'manager_id' => 24, 'admin_hr_id' => 28, 'it_staff_id' => 28, 'material_id' => 4,  'quantity' => 1,  'purpose' => 'Document event',          'status' => 'issued'],
            ['requester_id' => 30, 'manager_id' => 25, 'admin_hr_id' => 28, 'it_staff_id' => 28, 'material_id' => 11, 'quantity' => 1,  'purpose' => 'Tablet for presentation', 'status' => 'returned'],
        ];

        foreach ($requests as $r) {
            MaterialRequest::create($r);
        }

        // Add 15 random requests
        for ($i = 0; $i < 15; $i++) {
            MaterialRequest::create([
                'requester_id' => $employees->random()->id,
                'manager_id'   => $managers->random()->id,
                'admin_hr_id'  => rand(0,1) ? $admin->id : null,
                'it_staff_id'  => rand(0,2) ? $admin->id : null,
                'material_id'  => Material::inRandomOrder()->first()->id,
                'quantity'     => rand(1, 10),
                'purpose'      => fake()->sentence(),
                'status'       => fake()->randomElement([
                    'pending', 'manager_approved', 'manager_rejected',
                    'admin_approved', 'admin_rejected', 'issued', 'returned', 'cancelled'
                ]),
            ]);
        }
    }
}