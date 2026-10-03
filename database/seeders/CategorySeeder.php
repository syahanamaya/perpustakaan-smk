<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    $data = ['Novel', 'Komik', 'Buku Pelajaran', 'Ensiklopedia', 'Biografi', 'Sains', 'Agama'];

    foreach ($data as $d) {
        \App\Models\Category::create(['name' => $d]);
    }
}
}
