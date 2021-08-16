<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;

class EstadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        Estado::create(
        [
        'name' => 'RONDONIA',
        'uf' => 'RO',
        'ibge' => '11',
        ],
        );
        Estado::create(
        [
        'name' => 'ACRE',
        'uf' => 'AC',
        'ibge' => '12',
        ],
        );
        Estado::create(
        [
        'name' => 'AMAZONAS',
        'uf' => 'AM',
        'ibge' => '13',
        ],
        );
        Estado::create(
        [
        'name' => 'RoRAIMA',
        'uf' => 'RR',
        'ibge' => '14',
        ],
        );
        Estado::create(
        [
        'name' => 'PARA',
        'uf' => 'PA',
        'ibge' => '15',
        ],
        );
        Estado::create(
        [
        'name' => 'AMAPA',
        'uf' => 'AP',
        'ibge' => '16',
        ],
        );
        Estado::create(
        [
        'name' => 'TOCANTINS',
        'uf' => 'TO',
        'ibge' => '17',
        ],
        );
        Estado::create(
        [
        'name' => 'MARANHAO',
        'uf' => 'MA',
        'ibge' => '21',
        ],
        );
        Estado::create(
        [
        'name' => 'PIAUI',
        'uf' => 'PI',
        'ibge' => '22',
        ],
        );
        Estado::create(
        [
        'name' => 'CEARA',
        'uf' => 'CE',
        'ibge' => '23',
        ],
        );
        Estado::create(
        [
        'name' => 'RIO GRANDE DO NORTE',
        'uf' => 'RN',
        'ibge' => '24',
        ],
        );
        Estado::create(
        [
        'name' => 'PARAIBA',
        'uf' => 'PB',
        'ibge' => '25',
        ],
        );
        Estado::create(
        [
        'name' => 'PERNAMBUCO',
        'uf' => 'PE',
        'ibge' => '26',
        ],
        );
        Estado::create(
        [
        'name' => 'ALAGOAS',
        'uf' => 'AL',
        'ibge' => '27',
        ],
        );
        Estado::create(
        [
        'name' => 'SERGIPE',
        'uf' => 'SE',
        'ibge' => '28',
        ],
        );
        Estado::create(
        [
        'name' => 'BAHIA',
        'uf' => 'BA',
        'ibge' => '29',
        ],
        );
        Estado::create(
        [
        'name' => 'MINAS GERAIS',
        'uf' => 'MG',
        'ibge' => '31',
        ],
        );
        Estado::create(
        [
        'name' => 'ESPIRITO SANTO',
        'uf' => 'ES',
        'ibge' => '32',
        ],
        );
        Estado::create(
        [
        'name' => 'RIO DE JANEIRO',
        'uf' => 'RJ',
        'ibge' => '33',
        ],
        );
        Estado::create(
        [
        'name' => 'SAO PAULO',
        'uf' => 'SP',
        'ibge' => '35',
        ],
        );
        Estado::create(
        [
        'name' => 'PARANA',
        'uf' => 'PR',
        'ibge' => '41',
        ],
        );
        Estado::create(
        [
        'name' => 'SANTA CATARINA',
        'uf' => 'SC',
        'ibge' => '42',
        ],
        );
        Estado::create(
        [
        'name' => 'RIO GRANDE DO SUL',
        'uf' => 'RS',
        'ibge' => '43',
        ],
        );
        Estado::create(
        [
        'name' => 'MATO GROSSO DO SUL',
        'uf' => 'MS',
        'ibge' => '50',
        ],
        );
        Estado::create(
        [
        'name' => 'MATO GROSSO',
        'uf' => 'MT',
        'ibge' => '51',
        ],
        );
        Estado::create(
        [
        'name' => 'GOIAS',
        'uf' => 'GO',
        'ibge' => '52',
        ],
        );
        Estado::create(
        [
        'name' => 'DISTRITO FEDERAL',
        'uf' => 'DF',
        'ibge' => '53',
        ],
        );
    }
}
