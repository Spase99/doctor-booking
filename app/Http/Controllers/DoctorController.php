<?php

namespace App\Http\Controllers;

use App\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function profile(Request $request) {
        $doc = Doctor::find(Auth::user()->id);
        return view('backend.doctor.profile', compact('doc'));
    }

    public function updateProfile(Request $request) {
        $doc = Doctor::find(Auth::user()->id);
        $doc->fill($request->all());
        if (!empty($request->password)) {
            $doc->password = Hash::make($request->password);
        }
        $doc->save();

        return redirect(route('doctor.profile'));
    }
}
