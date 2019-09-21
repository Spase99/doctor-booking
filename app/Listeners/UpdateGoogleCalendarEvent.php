<?php

namespace App\Listeners;

use App\Appointment;
use App\Doctor;
use \App\Events\AppointmentCreated;
use App\Events\AppointmentModified;
use App\Services\GoogleCalendarManager;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\GoogleCalendar\Event;

class UpdateGoogleCalendarEvent
{

    public function __construct()
    {
        //
    }

    public function handle(AppointmentModified $event)
    {
        $request = $event->request;
        file_put_contents(storage_path("test/listen.txt"), "listener called");
        $channelId = $request->header('X_GOOG_CHANNEL_ID', null);

        if ($channelId === null) {
            return;
        }
        $doctor = Doctor::where('notification_channel_id', $channelId)->first();
        $googleCalendarId = $doctor->google_calendar_id;
        $calendarManager = new GoogleCalendarManager();
        $calendarManager->sync($googleCalendarId, $doctor);
    }
}
