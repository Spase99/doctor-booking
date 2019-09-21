<?php

use Illuminate\Support\Facades\DB;

class RoomTableSeeder extends \Illuminate\Database\Seeder {
    public function run() {
        DB::table('rooms')->insert([
            'name' => "Raum A"]);

        DB::table('rooms')->insert([
            'name' => "Raum B"]);
    }
}