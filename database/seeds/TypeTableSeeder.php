<?php

use Illuminate\Support\Facades\DB;

class TypeTableSeeder extends \Illuminate\Database\Seeder {
    public function run() {
        DB::table('types')->insert([
            'name' => "Standardtermin",
            'duration' => 30,
            'pay_type' => "Krankenkasse"]);

        DB::table('types')->insert([
            'name' => "Privattermin",
            'duration' => 45,
            'pay_type' => "Privat"]);
    }
}