<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'room_bookings';

    protected $dates = ["start", "end", "repeat_until"];

    public function upcomingEvents() {
        return $this->hasMany('App\Appointment')->whereDate('start', '>=', date('Y-m-d'));
    }
}
