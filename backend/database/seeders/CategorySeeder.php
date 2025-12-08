<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Computer & Accessories',
                'description' => 'Desktops, Laptops, Monitors, Keyboards, Mouse, Chargers, etc.'
            ],
            [
                'name' => 'Printing & Scanning',
                'description' => 'Printers, Scanners, Photocopiers, Toner cartridges.'
            ],
            [
                'name' => 'Networking Equipment',
                'description' => 'Wi-Fi Routers, Switches, Cables, Network adapters.'
            ],
            [
                'name' => 'Office Electronics',
                'description' => 'Projectors, Projector screens, Headsets, Microphones.'
            ],
            [
                'name' => 'Power & Cables',
                'description' => 'Extension cords, Power strips, HDMI cables, USB cables.'
            ],
            [
                'name' => 'Storage Devices',
                'description' => 'External hard drives, USB flash drives, SD cards.'
            ],
            [
                'name' => 'Stationery & Office Supplies',
                'description' => 'Notebooks, Pens, Folders, Paper, Staplers, Tape.'
            ],
            [
                'name' => 'Furniture & Fixtures',
                'description' => 'Chairs, Tables, Shelves, Cabinets.'
            ],
            [
                'name' => 'Safety & Protective Gear',
                'description' => 'Helmets, Gloves, Safety boots, High-visibility vests.'
            ],
            [
                'name' => 'Miscellaneous / Others',
                'description' => 'Items that don’t fit in other categories.'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
