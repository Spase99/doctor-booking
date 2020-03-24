<?php

use App\Type;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\GoogleCalendar\Event;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect(route('backend'));
});

// Google sends this
Route::post('/doc/update', 'ApiAppointmentController@update')->name('appointments.update'); 

Route::group(['prefix' => '/doc/{doctor}'], function () {
    Route::get('/', 'AppointmentController@index');
    Route::post('/add', 'ApiAppointmentController@add'); // TODO: should be POST to /
    Route::get('/freeappointments/{month}/', 'ApiAppointmentController@freeAppointments');
    Route::get('/list/{type}/{date}', 'ApiAppointmentController@list');
});

Route::group(['prefix' => 'backend', 'middleware' => ['auth']], function () {
    Route::get('/', function () {
        $user = Auth::user();
        $checkPermissions = [];
        $permissions = [];
        foreach ($checkPermissions as $checkPermission) {
            $permissions[$checkPermission] = $user->hasPermissionTo($checkPermission);
        }
        // Redirect reception users directly to the booking calender / room view.
        if($user->hasRole('reception')) {
            return redirect()->route('rooms.index');
        }        
        return view('backend.dashboard', compact('user', 'permissions'));
    })->name('backend');

    Route::get('/rooms', 'RoomController@index')->name('rooms.index');
    Route::get('/overdraft', 'RoomController@overdraft')->name('rooms.overdraft');
    Route::get('/booking', 'AppointmentController@receptionIndex')->name('booking.index');
    Route::get('/profile', 'DoctorController@profile')->name('doctor.profile');
    Route::put('/profile', 'DoctorController@updateProfile')->name('doctor.updateProfile');
});

Auth::routes(['register'=>false]);
