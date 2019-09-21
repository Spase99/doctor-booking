<?php

namespace App\Services;

use App\Booking;
use App\Exceptions\OverbookingException;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService {
    public function add($input, $user) {
        $start = $input['start'] ?? null;
        $end = $input['end'] ?? null;

        if (is_null($start) || is_null($end)) {
            throw new Exception("Start oder Ende nicht gesetzt");
        }

        $booking = new Booking();
        $booking->room_id = (int) ($input['room_id'] ?? 0);
        $booking->start = new CarbonImmutable($start);
        $booking->end = new CarbonImmutable($end);
        $booking->type_id = is_null($input['type_id']) ? null : (int) $input['type_id'];
        $booking->weekly_repeat = (bool) ($input['repeat'] ?? false);
        $booking->repeat_until = $booking->weekly_repeat ? Carbon::now()->addYears(20): null;
        $booking->doctor_id = (int) ($input['doctor_id'] ?? 0);
        $booking->booked_by = $user->id;


        if (!$user->hasPermissionTo('create overtime bookings') && is_null($booking->type_id )) {
            throw new Exception("Kann keine Überbuchung erstellen");
        }

        try {
            $this->checkForConflictingDates($start, $end, $booking->weekly_repeat, $booking->repeat_until, $booking->doctor_id, $booking->room_id);
            $booking->save();
            return $booking;
        } catch (\Exception $e) {
            throw new OverbookingException("Raumbuchung konnte nicht gespeichert werden.");
        }
    }

    private function checkForConflictingDates($start, $end, bool $weekly_repeat, $repeat_until, $doctor_id, $room_id) {
        // Modify start time to be 15 minutes earlier, so we can account for the mandatory break for different doctors
        $earlyStart = new Carbon($start);
        $earlyStart->subMinutes(15);
        $earlyStart = $earlyStart->format('Y-m-d H:i');
        $lateStop = new Carbon($end);
        $lateStop->addMinutes(15);
        $lateStop = $lateStop->format('Y-m-d H:i');
        $results = DB::select("SELECT * FROM room_bookings
WHERE
      room_id = :room_id AND 
      deleted_at IS NULL AND
(((:start < end)  and  (:stop > start) and doctor_id = :doctor_id) OR
((:earlystart < end)  and  (:latestop > start) and doctor_id != :doctor_id) OR 
((TIME(:start) < TIME(end))  and  (TIME(:stop) > TIME(start)) and doctor_id = :doctor_id and DATE(repeat_until) >= DATE(:start) AND DAYOFWEEK(:start) = DAYOFWEEK(start)) OR 
((TIME(:earlystart) < TIME(end))  and  (TIME(:latestop) > TIME(start)) AND TIME(:earlystart) < TIME(:latestop)and doctor_id != :doctor_id and DATE(repeat_until) >= DATE(:earlystart) AND DAYOFWEEK(:start) = DAYOFWEEK(start)))",
            ['start' => $start, 'stop' => $end, 'doctor_id' => $doctor_id, 'earlystart' => $earlyStart, 'latestop' => $lateStop, 'room_id' => $room_id]);

        if (count($results) > 0) {
            throw new OverbookingException("Conflicting Dates");
        }
    }

    public function setEndDate(Booking $bookingRecord, string $endDate) {
        $bookingRecord->repeat_until = new Carbon($endDate);
        $bookingRecord->save();
    }
}
