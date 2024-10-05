<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Size;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SizeColor extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = [
            ['name' => 'Small'],
            ['name' => 'Medium'],
            ['name' => 'Large'],
            ['name' => 'Extra Large'],
            ['name' => 'XXL'],
        ];
        Size::insert($sizes);
        $colors = [
            ['name' => 'Red'],
            ['name' => 'Green'],
            ['name' => 'Blue'],
            ['name' => 'Yellow'],
            ['name' => 'Black'],
            ['name' => 'White'],
            ['name' => 'Purple'],
            ['name' => 'Orange'],
            ['name' => 'Pink'],
            ['name' => 'Brown'],
            ['name' => 'Gray'],
            ['name' => 'Cyan'],
        ];
        Color::insert($colors);
    }
}
