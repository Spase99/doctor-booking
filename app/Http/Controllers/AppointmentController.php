<?php

namespace App\Http\Controllers;

use App\Services\AppointmentService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller {
    /**
     * @var AppointmentService
     */
    private $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(Request $request, $doctor_url) {
        $doctor = User::where('url_slug', $doctor_url)->first();
        $payTypes = $this->appointmentService->getPaymentTypes($doctor);

        $hasAppointmentsConfigured = $payTypes->isNotEmpty();
        $noTurninMessage = env('NO_TURNIN_MESSAGE', 'Bitte beachten Sie, dass Honorarnoten nicht von der Krankenkasse übernommen werden.');
        $payTypes = json_encode($payTypes); // Transform for JS

        return view('frontend.patient', compact('hasAppointmentsConfigured', 'doctor', 'payTypes', 'noTurninMessage'));
    }

    public function receptionIndex(Request $request) {
        $user = Auth::user();
        $permissions = $user->getAllPermissions();

        if($user->hasRole('reception')) {
            return view('booking.index', compact('user', 'permissions'));
        } else {
            return redirect()->route('backend');
        }
    }
}
