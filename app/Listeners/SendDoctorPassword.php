<?php

namespace App\Listeners;

use App\Mail\DoctorPasswordMail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendDoctorPassword
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $doctor = $event->doctor;
        $password = Str::random(8);
        $doctor->password = Hash::make($password);
        $doctor->save();
        Mail::to($doctor->email)->send(new DoctorPasswordMail($doctor, $password));
    }
}
