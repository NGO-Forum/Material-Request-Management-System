<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MaterialRequestSeeder::class,
            MaterialIssueRecordSeeder::class,
            MaterialRequestActionSeeder::class,
            MaterialReturnSeeder::class,
            MaterialStockMovementSeeder::class,
        ]);
    }
}