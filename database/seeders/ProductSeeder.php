<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Teclado mecânico', 'price' => 299.90, 'stock' => 15],
            ['name' => 'Mouse sem fio', 'price' => 89.90, 'stock' => 30],
            ['name' => 'Monitor 24 polegadas', 'price' => 899.90, 'stock' => 12],
            ['name' => 'Headset USB', 'price' => 159.90, 'stock' => 20],
            ['name' => 'Webcam Full HD', 'price' => 249.90, 'stock' => 18],
            ['name' => 'Hub USB-C', 'price' => 119.90, 'stock' => 25],
            ['name' => 'SSD 1 TB', 'price' => 499.90, 'stock' => 10],
            ['name' => 'Notebook 15 polegadas', 'price' => 3499.90, 'stock' => 8],
            ['name' => 'Suporte para notebook', 'price' => 129.90, 'stock' => 22],
            ['name' => 'Cabo HDMI 2 metros', 'price' => 39.90, 'stock' => 50],
            ['name' => 'Carregador USB-C', 'price' => 149.90, 'stock' => 17],
            ['name' => 'Mousepad grande', 'price' => 59.90, 'stock' => 35],
            ['name' => 'Pendrive 64 GB', 'price' => 49.90, 'stock' => 40],
            ['name' => 'Adaptador Bluetooth', 'price' => 69.90, 'stock' => 28],
            ['name' => 'Caixa de som Bluetooth', 'price' => 199.90, 'stock' => 14],
            ['name' => 'Roteador Wi-Fi', 'price' => 279.90, 'stock' => 16],
            ['name' => 'Switch Gigabit 8 portas', 'price' => 329.90, 'stock' => 9],
            ['name' => 'Filtro de linha', 'price' => 79.90, 'stock' => 26],
            ['name' => 'Cabo de rede 5 metros', 'price' => 29.90, 'stock' => 45],
            ['name' => 'Adaptador HDMI para VGA', 'price' => 54.90, 'stock' => 19],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                [
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                ],
            );
        }
    }
}
