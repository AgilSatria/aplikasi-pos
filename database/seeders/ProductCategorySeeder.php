<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan Ringan',
                'description' => 'Berbagai jenis snack, keripik, biskuit, dan camilan.',
            ],
            [
                'name' => 'Minuman',
                'description' => 'Minuman kemasan seperti air mineral, teh, kopi, dan soda.',
            ],
            [
                'name' => 'Sembako',
                'description' => 'Kebutuhan pokok seperti beras, gula, minyak, dan tepung.',
            ],
            [
                'name' => 'Produk Kebersihan',
                'description' => 'Sabun, deterjen, pembersih lantai, dan perlengkapan kebersihan.',
            ],
            [
                'name' => 'Perawatan Tubuh',
                'description' => 'Shampoo, pasta gigi, sabun mandi, dan skincare.',
            ],
            [
                'name' => 'Produk Bayi',
                'description' => 'Popok, susu bayi, tisu basah, dan perlengkapan bayi.',
            ],
            [
                'name' => 'Makanan Instan',
                'description' => 'Mie instan, sosis, nugget, dan makanan siap saji.',
            ],
            [
                'name' => 'Frozen Food',
                'description' => 'Makanan beku seperti nugget, bakso, dan kentang goreng.',
            ],
            [
                'name' => 'Alat Tulis',
                'description' => 'Pulpen, buku, pensil, dan perlengkapan sekolah.',
            ],
            [
                'name' => 'Produk Rumah Tangga',
                'description' => 'Peralatan rumah tangga dan kebutuhan dapur.',
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
