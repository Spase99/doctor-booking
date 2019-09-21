<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $dates = ['start','end'];
    protected $fillable = ['start', 'end', 'description', 'google_eventid'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getTitle()
    {
        return explode("\n", $this->description)[0];
    }
}
