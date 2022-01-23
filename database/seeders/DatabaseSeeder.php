<?php

namespace Database\Seeders;

use App\Models\Element;
use App\Models\Location;
use App\Models\Dependence;
use App\Models\Responsible;
use App\Models\Brand;
use App\Models\Group;
use App\Models\Device;
use App\Models\Period;
use App\Models\TypeOtherMaintenance;
use App\Models\Company;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionsTableSeeder::class);
        
        /*\App\Models\User::create([
            'names' => 'Administrador',
            'username' => 'admin',
            'password' => bcrypt(123),
            'official_position' => 'Administrador del sistema',
            'technical_support' => true
        ]);*/
/*
        Element::factory()->count(4)->create();
        Location::factory()->count(3)->create();
        Dependence::factory()->count(10)->create();
        Responsible::factory()->count(10)->create();
        Brand::factory()->count(10)->create();
        Group::factory()->count(3)->create();
        Device::factory()->count(20)->create();
        Period::factory()->count(1)->create();
        TypeOtherMaintenance::factory()->count(1)->create();
        Company::factory()->count(1)->create();*/
        
    }
}
