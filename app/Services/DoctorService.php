<?php
namespace App\Services;

use App\Doctor;
use App\User;

class DoctorService {
    public function add($input) {
        $doc = new User($input);
        $doc->password = "None";
        $doc->save();
        $doc->assignRole('doctor');
        if (isset($input['can_book']) && $input['can_book']) {
            $doc->assignRole('bookingDoctor');
        }

        return $doc;
    }

    public function update(\App\User $doctor, array $input) {
        $doctor->fill($input);
        $doctor->save();

        return $doctor;
    }
}