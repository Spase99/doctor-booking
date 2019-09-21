<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingTableSeeder extends \Illuminate\Database\Seeder {
    public function run() {
        $jetzt = \Carbon\CarbonImmutable::now()->setTime(9,0);
        DB::table('room_bookings')->insert([
            'room_id' => 1,
            'start' => $jetzt,
            'end' => $jetzt->addHours(10),
            'doctor_id' => 2,
            'type_id' => 1,
            'repeat_until' => $jetzt->addWeeks(12),
            'weekly_repeat' => true,
            'booked_by' => 1
        ]);

        $jetzt = \Carbon\CarbonImmutable::now()->subDays(3)->setTime(9,0);
        DB::table('room_bookings')->insert([
            'room_id' => 1,
            'start' => $jetzt,
            'end' => $jetzt->addHours(5),
            'doctor_id' => 3,
            'type_id' => 1,
            'repeat_until' => $jetzt->addWeeks(12),
            'weekly_repeat' => true,
            'booked_by' => 1
        ]);
    }
}