<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\Doctor;
use App\Events\AppointmentCreated;
use App\Events\AppointmentModified;
use App\Services\AppointmentService;
use App\Type;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiAppointmentController extends Controller {
    /**
     * @var AppointmentService
     */
    private $appointmentService;

    public function __construct(AppointmentService $appointmentService) {
        $this->appointmentService = $appointmentService;
    }

    public function add(Request $request, $doctor) {
        $doctor = User::role('doctor')->where('url_slug', $doctor)->first();

        $appointment = $this->appointmentService->add($request->all(), $doctor);

        event(new AppointmentCreated($appointment));
    }

        public function update(Request $request) {
        file_put_contents(storage_path("test/update.txt"), "hook received");
        event(new AppointmentModified($request));
    }

    public function types(Request $r) {
        $types = Type::all()->only(['id', 'name', 'duration', 'pay_type']);
        return response()->json($types);
    }

    public function list(Request $request, $doc_url, $type, $date) {
        try {
            $doctor = User::where('url_slug', $doc_url)->first();
            $response = $this->appointmentService->listFreeSlots($doctor, $type, $date);
        } catch (Exception $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        }

        return response()->json($response);
    }

    public function freeAppointments(Request $request, $doc_url, $month) {
        try {
            $doctor = User::where('url_slug', $doc_url)->first();
            $typeId = $request->get('type');
            $type = Type::where('id', $typeId)->first();
            $response = $this->appointmentService->getAppointments($doctor, $type, $month);
        } catch (Exception $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        }

        return response()->json($response);
    }
}
