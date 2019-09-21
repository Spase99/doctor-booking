<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{

    protected $fillable = ['name', 'doctor_id', 'duration', 'pay_type', 'invisible'];

    protected $casts = ['invisible' => 'boolean'];

    public static function getPayTypes() {
        return ['Krankenkasse', 'Privat'];
    }

    public function doctor() {
        $doctor = $this->belongsTo('App\Doctor', 'doctor_id');

        return $doctor;
    }
}
