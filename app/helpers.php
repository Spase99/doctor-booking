<?php

use Carbon\Carbon;

function split_workday($beginTime, $endTime, $minutes) {
    $begin = Carbon::now();
    $begin->setTimeFromTimeString($beginTime);
    $end = Carbon::now();
    $end->setTimeFromTimeString($endTime);
    $end->subMinutes($minutes); // last time for appointment

    $slots = [];
    $current = $begin->copy();
    while ($current <= $end) {
        $slotBegin = $current->copy();
        $current->addMinutes($minutes);
        $value = $slotBegin->format('H:i') . "-" . $current->format('H:i');
        array_push($slots, $value);
    }

    return $slots;
}

function unfold_events($events, $foldStart, $foldEnd) {
    $new_events = [];
    foreach ($events as $event) {
        $start = new Carbon($event->start);
        //$differenceInWeeks = $start->diffInWeeks($foldStart, false)+1;
        //$start->addWeeks($differenceInWeeks);

        $end = new Carbon($event->end);
        /* If it is a single event, no unfolding has to be done */
        if ($event->repeat_until === null) {
            $ev = ['date' => $start->format('Y-m-d'), 'start' => $start->format('H:i'), 'end'=>$end->format('H:i')];
            array_push($new_events, $ev);
            continue;
        }
        /* Otherwise increment weekly, until either end of month or repeat_until is reached */
        $repeat_until = new Carbon($event->repeat_until);
        while ($start <= $foldEnd && ($start <= $repeat_until || $start->isSameDay($repeat_until))) {
            $ev = ['date' => $start->format('Y-m-d'), 'start' => $start->format('H:i'), 'end'=>$end->format('H:i')];
            array_push($new_events, $ev);
            $start->addWeek();
        }
    }

    return $new_events;
}

function array_keyvalue($arr, $key) {
    return [$key => $arr[$key]];
}