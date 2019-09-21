<?php namespace App\Services;

use App\Appointment;
use App\Exceptions\OverbookingException;
use Carbon\Carbon;
use Google_Service_Calendar;
use Google_Service_Calendar_AclRule;
use Google_Service_Calendar_AclRuleScope;
use Google_Service_Calendar_Calendar;
use Google_Service_Calendar_Channel;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_FreeBusyRequest;
use Google_Service_Calendar_FreeBusyRequestItem;
use Illuminate\Support\Facades\URL;
use Spatie\GoogleCalendar\GoogleCalendarFactory;

class GoogleCalendarManager {
    public $service; //TODO: make private
    public $client; // TODO: make private

    public function __construct() {
        $config = config('google-calendar');
        $this->client = GoogleCalendarFactory::createAuthenticatedGoogleClient($config);
        $this->service = new Google_Service_Calendar($this->client);
    }

    public function addCalendar(Google_Service_Calendar_Calendar $calendar) {
        return $this->service->calendars->insert($calendar);
    }

    public function addEvent($appointment, Google_Service_Calendar_Event $event)
    {
        $calendarId = $appointment->doctor->google_calendar_id;
        if ($calendarId != null) {
            $item = new Google_Service_Calendar_FreeBusyRequestItem();
            $item->setId($calendarId);
            $freeBusyRequest = new Google_Service_Calendar_FreeBusyRequest();
            $freeBusyRequest->setTimeMin($appointment->start->toRfc3339String());
            $freeBusyRequest->setTimeMax($appointment->end->toRfc3339String());
            $freeBusyRequest->setTimeZone("Europe/Vienna");
            $freeBusyRequest->setItems([$item]);

            $response = $this->service->freebusy->query($freeBusyRequest);
            if (count($response->calendars[$calendarId]->busy) > 0) {
                throw new OverbookingException("Overbooking in Google Calendar");
            }
            $googleEvent = $this->service->events->insert($calendarId, $event);
            $appointment->google_eventid = $googleEvent->getId();
        }
    }

    public function list() {
        return $this->service->calendarList->listCalendarList();
    }

    public function share($calendar, $email) {
        $rule = new Google_Service_Calendar_AclRule();
        $scope = new Google_Service_Calendar_AclRuleScope();
        /*
        The type of the scope. Possible values are:

            "default" - The public scope. This is the default value.
            "user" - Limits the scope to a single user.
            "group" - Limits the scope to a group.
            "domain" - Limits the scope to a domain.
        */
        $scope->setType("user");
        $scope->setValue($email);
        $rule->setScope($scope);
        /*
        The role assigned to the scope. Possible values are:
            "none" - Provides no access.
            "freeBusyReader" - Provides read access to free/busy information.
            "reader" - Provides read access to the calendar. Private events will appear to users with reader access, but event details will be hidden.
            "writer" - Provides read and write access to the calendar. Private events will appear to users with writer access, and event details will be visible.
            "owner" - Provides ownership of the calendar. This role has all of the permissions of the writer role with the additional ability to see and manipulate ACLs.
        */
        $rule->setRole("writer");
        return $this->service->acl->insert($calendar->id, $rule);
    }

    public function setUpPushNotifications($calendar)
    {
        $callbackUrl = "https://booking.healthforlife.at/doc/update"; //URL::to(route('appointments.update'));
        $channel =  new Google_Service_Calendar_Channel($this->client);
        $channel->setId(md5(time()));
        $channel->setType('web_hook');
        $channel->setAddress($callbackUrl);

        $this->service->events->watch($calendar->getId(), $channel);

        return $channel->getId();
    }

    public function sync($googleCalendarId, $doctor)
    {
        file_put_contents(storage_path("test/sync.txt"), "called");
        $syncToken = $doctor->sync_token;
        if ($syncToken !== null) {
            $changedEvents = $this->service->events->listEvents($googleCalendarId, ['syncToken' => $syncToken]);
        } else {
            $changedEvents = $this->service->events->listEvents($googleCalendarId);
        }
        $nextSyncToken = $changedEvents->nextSyncToken;
        file_put_contents(storage_path("test/sync_start.txt"), "starting sync");

        foreach ($changedEvents->items as $event) {
            $appointment = Appointment::firstOrNew(['google_eventid' => $event->id]);

            if ($event->status === 'cancelled') {
                $appointment->delete();
            } else {
                $appointment->start = new Carbon($event->start->dateTime);
                $appointment->end = new Carbon($event->end->dateTime);
                $appointment->description = $event->description;
                $appointment->doctor_id = $doctor->id;
                $appointment->save();
            }
        }
        file_put_contents(storage_path("test/sync_complete.txt"), "finishing sync");
        $doctor->sync_token = $nextSyncToken;
        $doctor->save();
    }

}