<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder {
    public function run() {
        DB::table('users')->insert([
            'name' => "Admin",
            'email' => 'admin@example.org',
            'password' => bcrypt('secret'),
            'phone' => '555 12345'
        ]);

        DB::table('users')->insert([
            'name' => "Dr. Arzt 1",
            'email' => 'arzt1@example.org',
            'password' => bcrypt('secret'),
            'phone' => '555 2222',
            'url_slug' => 'arzt-1'
        ]);

        DB::table('users')->insert([
            'name' => "Dr. Böhm",
            'email' => 'arzt2@example.org',
            'password' => bcrypt('secret'),
            'phone' => '555 3333',
            'url_slug' => 'dr-boehm'
        ]);

        DB::table('users')->insert([
            'name' => "Empfang",
            'email' => 'reception@example.org',
            'password' => bcrypt('secret'),
            'phone' => '555 12344'
        ]);

        $admin = User::find(1);
        $admin->assignRole('admin');

        $doc = User::find(2);
        $doc->assignRole('doctor');

        $doc = User::find(3);
        $doc->assignRole('doctor');
        $doc->assignRole('bookingDoctor');

        $reception = User::find(4);
        $reception->assignRole('reception');
    }
}