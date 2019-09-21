<?php

namespace App\Http\Controllers;

use App\Exceptions\OverbookingException;
use App\Opening;
use App\OpeningException;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiOpeningController extends Controller
{
    public function listOpenings(Request $request) {
        $start = $request->get('start');
        $end = $request->get('end');

        $openings = Opening::whereNotIn('id', DB::table('opening_exceptions')
            ->where('exception_date', '>=', $start)
            ->where('exception_date', '<=', $end)
            ->pluck('opening_id')
        )->where(function($q) use($start, $end) {
            $q->where(function($q1) use ($start, $end) {
                $q1->where('start', '>=', $start)
                    ->where('end', '<=', $end);
            }) // single events
            ->orWhere(function($q2) use ($start, $end) {
                $q2->where('weekly_repeat', '=', 1)
                    ->where('start', '<=', $end)
                    ->where(function($q3) use ($start, $end) {
                        $q3->where('repeat_until', '>=', $start);
                    });
            }); // weekly repeating events
        })->get();


        for($i = 0; $i < count($openings); $i++) {
            $startDate = new Carbon($openings[$i]->start);
            $endDate = new Carbon($openings[$i]->end);
            $repeatUntil = new Carbon($openings[$i]->repeat_until);
            $openings[$i]->dow = [$startDate->dayOfWeek];
            $openings[$i]->start = $startDate->format('H:i');
            $openings[$i]->end = $endDate->isMidnight() ? '23:59' : $endDate->format('H:i');
            if ($openings[$i]->weekly_repeat == 1) {
                $openings[$i]->ranges = [[
                    "start" => $startDate->format('Y-m-d'),
                    "end" => $repeatUntil->format('Y-m-d')
                ]];
            }
        }

        return response()->json($openings);
    }

    public function addOpening(Request $request) {
        $start = $request->get('start') ?? null;
        $end = $request->get('end') ?? null;

        if (is_null($start) || is_null($end)) {
            throw new Exception("Start oder Ende nicht gesetzt");
        }

        $opening = new Opening();
        $opening->start = new CarbonImmutable($start);
        $opening->end = new CarbonImmutable($end);
        $opening->weekly_repeat = $request->get('weekly_repeat');
        $opening->repeat_until = $opening->weekly_repeat ? Carbon::now()->addYears(20): null;
        $opening->save();
        return $opening;
    }

    public function delete(Request $request, Opening $opening) {
        $opening->delete();
    }

    public function addException(Request $request, Opening $opening) {
        $exception = new OpeningException();
        $exception->exception_date = new Carbon($request->get('exception_date'));
        $exception->opening_id = $opening->id;

        $exception->save();
    }

    public function deleteException(Request $request, OpeningException $exception) {
        $exception->delete();
    }

    public function getAllExceptions(Request $request) {
        $exceptions = OpeningException::where('exception_date', '>=', Carbon::now()->subDays(7))->get();
        return response()->json($exceptions);
    }

    public function updateOpening(Request $request, Opening $opening) {
        $opening->start = $request->get('start', $opening->start);
        $opening->end = $request->get('end', $opening->end);
        $opening->repeat_until = $request->get('repeat_until', $opening->repeat_until);
        $opening->save();
    }

    public function isEventInOpenings(Request $request) {
        $start = new Carbon($request->input('start'));
        $end = new Carbon($request->input('end'));

        // select openings which completely encapsulate the event

        $openings = DB::select("SELECT * FROM openings
WHERE
    ((
        start <= :start AND
        end >= :end
    ) OR (
        weekly_repeat = 1 AND
        repeat_until >= :end AND
        TIME(:start) >= TIME(start) AND 
        TIME(:end) <= TIME(end) AND 
        DAYOFWEEK(:start) = DAYOFWEEK(start) AND 
        DAYOFWEEK(:end) = DAYOFWEEK(end)
    )) 
    AND id NOT IN (SELECT opening_id FROM opening_exceptions WHERE exception_date >= DATE(:start) AND exception_date <= DATE(:end))",
        ['start' => $start, 'end' => $end]);

        if(count($openings) == 0) {
            throw new OverbookingException("Die Raumbuchung befindet sich nicht innerhalb der Öffnungszeiten.");
        } else {
            return response()->json('OK', 200);
        }
    }
}
