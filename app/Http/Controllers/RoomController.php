<?php

namespace App\Http\Controllers;

use App\Type;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index(Request $request) {
        $appointmentTypes = Type::all();

        $user = Auth::user();
        if ($user->hasPermissionTo('create bookings')) {
            if ($user->hasPermissionTo('list doctors')) {
                $selectableDoctors = User::role('doctor')->get();
            } else {
                $selectableDoctors = [$user];
            }
        } else {
            $selectableDoctors = [];
        }

        $selectableDoctors = json_encode($selectableDoctors);

        $checkPermissions = ['create bookings', 'select bookings', 'create repeating bookings', 'list types',
            'update bookings', 'add booking exceptions', 'update booking exceptions', 'delete booking exceptions',
            'delete own bookings', 'delete bookings', "update own bookings", 'add own booking exceptions'];
        $permissions = [];
        foreach ($checkPermissions as $checkPermission) {
            $permissions[$checkPermission] = $user->hasPermissionTo($checkPermission);
        }

        return view('rooms.index', compact('appointmentTypes', 'selectableDoctors', 'user', 'permissions'));
    }

    public function overdraft(Request $request) {
        $appointmentTypes = [];

        $user = Auth::user();
        if ($user->hasPermissionTo('create bookings')) {
            if ($user->hasPermissionTo('list doctors')) {
                $selectableDoctors = User::role('doctor')->get();
            } else {
                $selectableDoctors = [$user];
            }
        } else {
            $selectableDoctors = [];
        }

        $selectableDoctors = json_encode($selectableDoctors);

        $checkPermissions = ['create bookings', 'select bookings',
            'update bookings', 'delete own bookings', "update own bookings", 'add own booking exceptions'];
        $permissions = [];
        foreach ($checkPermissions as $checkPermission) {
            $permissions[$checkPermission] = $user->hasPermissionTo($checkPermission);
        }

        return view('rooms.overdraft', compact('appointmentTypes', 'selectableDoctors', 'user', 'permissions'));
    }
}
