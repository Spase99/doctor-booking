<?php

namespace App\Services;

use App\Appointment;
use App\Doctor;
use App\Events\AppointmentCreated;
use App\Exceptions\OverbookingException;
use App\Type;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\GoogleCalendar\Event;

class AppointmentService {
    public function listFreeSlots($doctor, $type, $date) {
        try {
            $type = Type::with('doctor')->findOrFail($type);

            if ($type->doctor === null || $type->doctor->url_slug !== $doctor->url_slug) {
                throw new Exception("Termintyp passt nicht zu Arzt.");
            }

            $timeslots = split_workday("08:30", "15:00", $type->duration);


        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Ungültiger Termintyp.");
        }

        return $timeslots;
    }

    public function getAppointmentTypes($doctor, $payType) {
        $monthBegin = (new Carbon())->startOfMonth();
        $monthEnd = (new Carbon())->endOfMonth();

        $appointmentTypes = DB::select("SELECT DISTINCT types.id, types.name, types.duration, types.invisible FROM room_bookings 
JOIN types ON room_bookings.type_id = types.id
WHERE room_bookings.deleted_at IS NULL AND types.pay_type = :pay_type AND doctor_id = :doctor_id AND ((DATE(start) >= DATE(:start) AND DATE(end) <= DATE(:end)) 
                                                                           OR (DATE(end) <= DATE(:end) AND repeat_until >= DATE(:start)))",
            ['doctor_id' => $doctor->id, 'pay_type' => $payType, 'start' => $monthBegin->format('Y-m-d'), 'end' => $monthEnd->format('Y-m-d')]);

        //$appointmentTypes = array_map(function ($payType) { return $payType->pay_type;}, $appointmentTypes);

        return $appointmentTypes;
    }

    public function getPaymentTypes($doctor) {
        $monthBegin = (new Carbon())->startOfMonth();
        $monthEnd = (new Carbon())->endOfMonth();

        $payTypes = DB::select("SELECT DISTINCT types.pay_type FROM room_bookings 
JOIN types ON room_bookings.type_id = types.id
WHERE room_bookings.deleted_at IS NULL AND doctor_id = :doctor_id AND ((DATE(start) >= DATE(:start) AND DATE(end) <= DATE(:end)) OR (DATE(end) <= DATE(:end) AND repeat_until >= DATE(:start)))",
            ['doctor_id' => $doctor->id, 'start' => $monthBegin->format('Y-m-d'), 'end' => $monthEnd->format('Y-m-d')]);

        $payTypes = array_map(function ($payType) {
            return $payType->pay_type;
        }, $payTypes);

        return collect($payTypes);
    }

    public function getSlotsForMonth(string $month, $doctor, $type) {
        $monthBegin = (new Carbon($month))->startOfMonth();
        $monthEnd = (new Carbon($month))->endOfMonth();
        $bookings = DB::select("SELECT start, end, repeat_until FROM room_bookings 
WHERE deleted_at IS NULL AND doctor_id = :doctor_id AND type_id = :type_id AND ((DATE(start) >= DATE(:start) AND DATE(end) <= DATE(:end)) OR (DATE(end) <= DATE(:end) AND repeat_until >= DATE(:start)))",
            ['doctor_id' => $doctor->id, 'type_id' => $type->id, 'start' => $monthBegin->format('Y-m-d'), 'end' => $monthEnd->format('Y-m-d')]);
        $bookings = unfold_events($bookings, $monthBegin, $monthEnd);

        $result = [];
        foreach ($bookings as $booking) {
            $slots = split_workday($booking['start'], $booking['end'], $type->duration);
            if (isset ($result[$booking['date']]) && is_array($result[$booking['date']])) {
                $result[$booking['date']] = array_merge($result[$booking['date']], $slots);
            } else {
                $result[$booking['date']] = $slots;
            }

        }

        return $result;
    }

    public function getAppointments($doctor, $type, $month) {
        $slots = $this->getSlotsForMonth($month, $doctor, $type);
        $monthBegin = (new Carbon($month))->startOfMonth();
        $monthEnd = (new Carbon($month))->endOfMonth();

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->where('start', '>=', $monthBegin)
            ->where('end', '<=', $monthEnd)
            ->get();
        $response = [];
        foreach ($slots as $date => $times) {
            $start = new Carbon($date);
            $end = $start->copy();

            foreach ($times as $slot) {
                $isFree = true;
                list($startTime, $endTime) = explode("-", $slot);
                $start->setTimeFromTimeString($startTime);
                $end->setTimeFromTimeString($endTime);

                foreach ($appointments as $appointment) {
                    $hasOverlap = max($appointment->start, $start) < min($appointment->end, $end);
                    if ($hasOverlap) {
                        $isFree = false;
                        break;
                    }
                }
                if ($isFree) {
                    if (isset($response[$date])) {
                        array_push($response[$date], $slot);
                    } else {
                        $response[$date] = [$slot];
                    }
                }
            }
        }
        return $response;
    }

    public function getAppointmentInDays(int $days, bool $exactDay = true) {
        $operator = $exactDay ? '=' : '<=';

        $today = CarbonImmutable::today();
        $targetDate = $today->addDays($days);

        return Appointment::whereDate('start', $operator, $targetDate)->get();
    }

    private function checkForConflictingDates($start, $end, $doctor_id) {
        $results = DB::select("SELECT * FROM appointments
WHERE deleted_at IS NULL 
  AND (:start < end)  and  (:stop > start) and doctor_id = :doctor_id",
            ['doctor_id' => $doctor_id, 'start' => $start, 'stop' => $end]);

        if (count($results) > 0) {
            throw new OverbookingException("Conflicting Dates");
        }
    }

    public function add(array $input, $doctor) {
        $this->checkForConflictingDates($input['start'], $input['end'], $doctor->id);

        $type = Type::find((int)$input['type']);
        $appointment = new Appointment();
        $appointment->start = new Carbon($input['start']);
        $appointment->end = new Carbon($input['end']);
        $appointment->doctor_id = $doctor->id;
        $patientName = $input['first_name'] . " " . $input['last_name'];
        $appointment->name = $patientName;
        $appointment->email = $input['email'];
        $appointment->description = $patientName . "\n"
            . $type->name . "\n"
            . "Tel: " . $input['phone'] . "\n"
            . "E-Mail: " . $appointment->email;

        $results = DB::select("SELECT id FROM room_bookings WHERE 
                                   doctor_id = :doctor_id AND deleted_at IS NULL
                               AND TIME(:appoint_start) >= TIME(start) AND TIME(:appoint_end) <= TIME(end)
                               AND (
                                   DATE(:appoint_start) = DATE(NOW())
                                       OR 
                                   (WEEKDAY(:appoint_start) = WEEKDAY(start) AND DATE(start) <= DATE(:appoint_start) 
                                    AND weekly_repeat = 1 AND DATE(repeat_until) >= DATE(NOW()))
                                ) LIMIT 1", ["doctor_id" => $doctor->id, "appoint_start" => $appointment->start,
            "appoint_end" => $appointment->end]);

        $appointment->booking_id = !is_null($results[0]->id) ? $results[0]->id : null;

        $googleEvent = new Event();
        $googleEvent->name = $appointment->getTitle();
        $googleEvent->description = $appointment->description;
        $googleEvent->startDateTime = $appointment->start;
        $googleEvent->endDateTime = $appointment->end;
        $manager = new GoogleCalendarManager();
        $manager->addEvent($appointment, $googleEvent->googleEvent);
        $appointment->save();

        return $appointment;
    }
}
