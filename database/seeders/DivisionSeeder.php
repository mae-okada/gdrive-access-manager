<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $list = [
            'Project Manager',
            'Designer',
            'Sales',
            'Frontend',
            'Backend',
            'Mobile',
            'QA',
        ];

        $data = array_map(function ($name) {
            return ['name' => $name];
        }, $list);

        Division::factory()->createMany($data);
    }
}
