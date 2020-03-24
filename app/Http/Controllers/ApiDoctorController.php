<?php

namespace App\Http\Controllers;

use App\Doctor;
use App\Events\DoctorCreated;
use App\Services\AppointmentService;
use App\Services\DoctorService;
use App\Services\GoogleCalendarManager;
use App\Type;
use App\User;
use Carbon\Carbon;
use Google_Service_Calendar;
use Google_Service_Calendar_AclRule;
use Google_Service_Calendar_AclRuleScope;
use Google_Service_Calendar_Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\GoogleCalendar\GoogleCalendarFactory;

class ApiDoctorController extends Controller {
    /**
     * @var DoctorService
     */
    private $doctorService;
    /**
     * @var AppointmentService
     */
    private $appointmentService;

    public function __construct(DoctorService $doctorService, AppointmentService $appointmentService) {
        $this->doctorService = $doctorService;
        $this->appointmentService = $appointmentService;
    }

    public function list() {
        $doctors = User::with('roles')->role('doctor')->get();

        $doctors = $doctors->map(function ($doctor) {
            $attributes = $doctor->only(['id', 'name', 'email', 'phone', 'url_slug', 'can_turn_in']);
            $roles = $doctor->roles->pluck('name');
            $attributes['can_book'] = $roles->contains('bookingDoctor');

            return $attributes;
        });
        return response()->json($doctors);
    }

    public function payTypes(User $doctor) {
        $payTypes = $this->appointmentService->getPaymentTypes($doctor);

        return response()->json($payTypes);
    }

    public function appointmentTypes(Request $request, User $doctor, string $payType) {
        $apppointmentTypes = $this->appointmentService->getAppointmentTypes($doctor, $payType);

        return response()->json($apppointmentTypes);
    }

    public function delete(User $doctor) {
        if (!$doctor->hasRole('doctor')) {
            response()->json(['error' => true]);
        }

        return response()->json(['ok' => $doctor->delete()]);
    }

    public function add(Request $request) {
        $calendarManager = new GoogleCalendarManager();
        $calendar = new Google_Service_Calendar_Calendar();
        $google_account = $request->get('google_account');
        $secretaryAccount = env('GOOGLE_CAL_ADDRESS');

        $doctor = $this->doctorService->add($request->all());

        $calendar->setSummary($request->name);
        $calendar->setTimeZone('Europe/Vienna');
        $calendar = $calendarManager->addCalendar($calendar);
        $doctor->google_calendar_id = $calendar->getId();
        if ($google_account) {
            $calendarManager->share($calendar, $google_account);
        }
        $calendarManager->share($calendar, $secretaryAccount);
        $doctor->notification_channel_id = $calendarManager->setUpPushNotifications($calendar);
        if ($doctor->save()) {
            event(new DoctorCreated($doctor));
        }

        return response()->json($doctor->only('id', 'name', 'email', 'phone', 'url_slug', 'can_turn_in'));
    }

    public function update(Request $request, User $doctor) {
        if (!$doctor->hasRole('doctor')) {
            response()->json(['error' => true]);
        }
        $infos = $request->get('doctor');

        $doctor = $this->doctorService->update($doctor, $infos);
        if ($infos['can_book']) {
            $doctor->assignRole('bookingDoctor');
        } else {
            $doctor->removeRole('bookingDoctor');
        }

        return response()->json($doctor->only('id', 'name', 'email', 'phone', 'url_slug'));
    }
    
}
