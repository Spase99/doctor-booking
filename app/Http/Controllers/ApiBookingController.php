<?php

namespace App\Http\Controllers;

use App\BookingException;
use App\Doctor;
use App\Services\BookingService;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;


use App\Booking;
use App\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiBookingController extends Controller {
    /**
     * @var BookingService
     */
    private $bookingService;

    public function __construct(BookingService $bookingService) {
        $this->bookingService = $bookingService;
    }

    public function listBookings(Request $request, Room $room) {
        $user = Auth::user();

        $start = $request->get('start');
        $end = $request->get('end');
        $bookings = DB::select("SELECT room_bookings.id as booking_id, start, end, 
        concat(doctors.name, ' (', rooms.name, ') ', '[', COALESCE(types.name, '-'), ']') as title, room_id, weekly_repeat, repeat_until, doctor_id, booked_by, type_id as type
        FROM room_bookings
        JOIN users as doctors ON room_bookings.doctor_id = doctors.id
        JOIN rooms ON room_bookings.room_id = rooms.id
        LEFT JOIN types ON room_bookings.type_id = types.id
        WHERE room_bookings.room_id = ? AND
        room_bookings.deleted_at IS NULL AND ((weekly_repeat = 0 AND room_bookings.start >= ? AND room_bookings.end <= ?) /* single booking */
        OR (weekly_repeat = 1 AND room_bookings.start <= ? AND (room_bookings.repeat_until >= ? || room_bookings.repeat_until <= ?))) /* mutliple booking */
          AND room_bookings.id NOT IN (SELECT booking_id FROM booking_exceptions WHERE DATE(exception_date) >= ? AND DATE(exception_date) <= ?) 
        ",
            [$room->id, $start, $end, $end, $start, $end, $start, $end]); // should be begin of week, not now

        $length = count($bookings);
        for ($i = 0; $i < $length; $i++) {
            $startDate = new Carbon($bookings[$i]->start);
            $endDate = new Carbon($bookings[$i]->end);
            $repeatUntil = new Carbon($bookings[$i]->repeat_until);
            if ($bookings[$i]->weekly_repeat == 1) {
                $bookings[$i]->dow = [$startDate->dayOfWeek];
                $bookings[$i]->start = $startDate->format('H:i');
                $bookings[$i]->end = $endDate->isMidnight() ? '23:59' : $endDate->format('H:i');
                $bookings[$i]->ranges = [[
                    "start" => $startDate->format('Y-m-d'),
                    "end" => $repeatUntil->format('Y-m-d')
                ]];
            }
            if (!($user->hasPermissionTo('see booking names') || $user->id == $bookings[$i]->doctor_id)) {
                $bookings[$i]->title = 'Gebucht';
                $bookings[$i]->doctor_id = null;
                $bookings[$i]->booked_by = null;
            }
        }

        return response()->json($bookings);
    }

    public function addBooking(Request $request) {
        $this->bookingService->add($request->all(), Auth::user());
    }

    public function updateBooking(Request $request, Booking $booking) {
        $booking->start = $request->get('start', $booking->start);
        $booking->end = $request->get('end', $booking->end);
        $booking->repeat_until = $request->get('repeat_until', $booking->repeat_until);
        $booking->save();
    }

    public function delete(Request $request, Booking $booking) {
        if ($booking->upcomingEvents->count() != 0) {
            throw new \Exception("Es gibt bereits künftige Buchungen an diesem Termin");
        }
        $booking->delete();
    }

    public function addException(Request $request, Booking $booking) {
        $exception = new BookingException();
        $exception->exception_date = new Carbon($request->get('exception_date'));
        $exception->booking_id = $booking->id;

        // Check conflicts with appointments
        $upcoming = $booking->upcomingEvents;
        foreach ($upcoming as $appointment) {
            if ($appointment->start->format('Y-m-d') == $exception->exception_date->format('Y-m-d')) {
                throw new \Exception("Es gibt bereits künftige Buchungen an diesem Termin");
            }
        }

        if ($exception->save()) {
            return response()->json(['ok' => true]);
        } else {
            return response()->json(['error' => true]);
        }
    }

    public function deleteException(Request $request, BookingException $exception) {
        $exception->delete();
    }

    public function getExceptions(Request $request, Doctor $doctor) {
        $exceptions = DB::select("SELECT booking_exceptions.id as exception_id, booking_id, DATE_FORMAT(exception_date, '%d.%m.%Y') as exception_date, TIME_FORMAT(room_bookings.start, '%H:%i') as start, 
       TIME_FORMAT(room_bookings.end, '%H:%i') as end FROM booking_exceptions
JOIN room_bookings ON booking_exceptions.booking_id = room_bookings.id
JOIN users ON room_bookings.doctor_id = users.id
WHERE users.id = :doctor_id AND exception_date >= NOW() - INTERVAL 7 DAY", ['doctor_id' => $doctor->id]);

        return response()->json($exceptions);
    }

    public function getAllExceptions(Request $request) {
        $exceptions = DB::select("SELECT booking_exceptions.id as exception_id, booking_id, 
       DATE_FORMAT(exception_date, '%d.%m.%Y') as exception_date, TIME_FORMAT(room_bookings.start, '%H:%i') as start, 
       TIME_FORMAT(room_bookings.end, '%H:%i') as end, users.name as doctor_name FROM booking_exceptions
JOIN room_bookings ON booking_exceptions.booking_id = room_bookings.id
JOIN users ON room_bookings.doctor_id = users.id
WHERE exception_date >= NOW() - INTERVAL 7 DAY");

        return response()->json($exceptions);
    }
}