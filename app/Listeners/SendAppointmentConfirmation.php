<?php

namespace App\Listeners;

use \App\Events\AppointmentCreated;
use App\Mail\AppointmentConfirmationMail;
use App\Services\GoogleCalendarManager;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Spatie\GoogleCalendar\Event;

class SendAppointmentConfirmation
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
     * @param  AppointmentCreated  $event
     * @return void
     */
    public function handle(AppointmentCreated $event)
    {
        $appointment = $event->appointment;
        if($appointment && $appointment->email) {
            Mail::to($appointment->email)->send(new AppointmentConfirmationMail($appointment));
        }

    }
}
