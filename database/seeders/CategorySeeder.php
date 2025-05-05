<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Kategori Makanan
            [
                'nama' => 'Makanan Berat',
                'deskripsi' => 'Kategori untuk makanan utama seperti nasi, mie, dan lauk pauk',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Makanan Ringan',
                'deskripsi' => 'Kategori untuk cemilan dan snack',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Minuman',
                'deskripsi' => 'Kategori untuk berbagai jenis minuman',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Kue dan Roti',
                'deskripsi' => 'Kategori untuk aneka kue, roti, dan pastry',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Kategori Kecantikan
            [
                'nama' => 'Perawatan Wajah',
                'deskripsi' => 'Kategori untuk produk perawatan wajah',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Perawatan Tubuh',
                'deskripsi' => 'Kategori untuk produk perawatan tubuh',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Makeup',
                'deskripsi' => 'Kategori untuk produk makeup dan kosmetik',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Perawatan Rambut',
                'deskripsi' => 'Kategori untuk produk perawatan rambut',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        Category::insert($categories);
    }
}
