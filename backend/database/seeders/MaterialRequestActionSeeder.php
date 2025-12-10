<?php
// database/seeders/MaterialRequestActionSeeder.php

namespace Database\Seeders;

use App\Models\MaterialRequestAction;
use App\Models\MaterialRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialRequestActionSeeder extends Seeder
{
    public function run(): void
    {
        $actors = User::whereIn('role_id', [1, 2])->get(); // Admin + Managers
        $actionTypes = [
            'manager_approved'   => 'Approved by manager',
            'manager_rejected'   => 'Rejected by manager',
            'admin_approved'     => 'Approved by Admin/IT',
            'admin_rejected'     => 'Rejected by Admin',
            'it_review'          => 'IT reviewed availability',
            'issued'             => 'Material issued',
            'comment'            => 'Additional comment added',
            'returned'           => 'Material returned',
        ];

        MaterialRequest::all()->each(function ($request) use ($actors, $actionTypes) {
            $numActions = rand(1, 4);
            $usedTypes = [];

            for ($i = 0; $i < $numActions; $i++) {
                $type = array_rand($actionTypes);
                while (in_array($type, $usedTypes)) {
                    $type = array_rand($actionTypes);
                }
                $usedTypes[] = $type;

                MaterialRequestAction::create([
                    'request_id'  => $request->id,
                    'action_by'   => $actors->random()->id,
                    'action_type' => $type,
                    'remarks'     => $actionTypes[$type],
                ]);
            }
        });
    }
}