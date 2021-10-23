<?php

namespace Database\Seeders;

use App\Models\Assinatura;
use Illuminate\Database\Seeder;

class AssinaturasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Assinatura::create([
            'installments' => "3",
            'name' => "Aperitivo",
            'price' => "300",
            'month_duration' => "3",
            'numbers_lojas' => "1"
        ]);
        Assinatura::create([
            'installments' => "6",
            'name' => "Aperitivo",
            'price' => "500",
            'month_duration' => "6",
            'numbers_lojas' => "1"
        ]);
        Assinatura::create([
            'installments' => "12",
            'name' => "Aperitivo",
            'price' => "1100",
            'month_duration' => "12",
            'numbers_lojas' => "1"
        ]);
        Assinatura::create([
            'installments' => "3",
            'name' => "Entrada",
            'price' => "450",
            'month_duration' => "3",
            'numbers_lojas' => "2"
        ]);
        Assinatura::create([
            'installments' => "6",
            'name' => "Entrada",
            'price' => "750",
            'month_duration' => "6",
            'numbers_lojas' => "2"
        ]);
        Assinatura::create([
            'installments' => "12",
            'name' => "Entrada",
            'price' => "1650",
            'month_duration' => "12",
            'numbers_lojas' => "2"
        ]);
        Assinatura::create([
            'installments' => "3",
            'name' => "Prato Principal",
            'price' => "900",
            'month_duration' => "3",
            'numbers_lojas' => "3"
        ]);
        Assinatura::create([
            'installments' => "6",
            'name' => "Prato Principal",
            'price' => "1500",
            'month_duration' => "6",
            'numbers_lojas' => "3"
        ]);
        Assinatura::create([
            'installments' => "12",
            'name' => "Prato Principal",
            'price' => "3300",
            'month_duration' => "12",
            'numbers_lojas' => "3"
        ]);
    }
}
