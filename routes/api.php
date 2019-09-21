<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => '/doc/{doctor}'], function () {
    Route::get('/', 'AppointmentController@index');
    Route::post('/', 'ApiAppointmentController@add');
    Route::get('/freeappointments/{month}/', 'ApiAppointmentController@freeAppointments');
});

Route::group(['prefix' => '/bookings'], function () {
    Route::post('/', 'ApiBookingController@addBooking');
    Route::get('/exceptions/', 'ApiBookingController@getAllExceptions');
    Route::put('/update/{booking}', 'ApiBookingController@updateBooking');
    Route::put('/setEndDate/{booking}', 'ApiBookingController@sendEndDate');
    Route::put('/exceptDate/{booking}', 'ApiBookingController@addException');
    Route::get('/exceptions/{doctor}', 'ApiBookingController@getExceptions');
    Route::delete('/exceptions/{exception}', 'ApiBookingController@deleteException');
    Route::delete('/delete/{booking}', 'ApiBookingController@delete');
    Route::get('/{room}', 'ApiBookingController@listBookings');
});



Route::group(['prefix' => 'rooms'], function () {
    Route::get('/', 'ApiRoomController@list');
    Route::post('/', 'ApiRoomController@add');
    Route::put('{room}', 'ApiRoomController@update');
    Route::delete('{room}', 'ApiRoomController@delete');
});

Route::group(['prefix' => 'doctors'], function () {
    Route::get('/', 'ApiDoctorController@list');
    Route::post('/', 'ApiDoctorController@add');
    Route::get('{room}', 'ApiDoctorController@show');
    Route::put('{doctor}', 'ApiDoctorController@update');
    Route::delete('{doctor}', 'ApiDoctorController@delete');
});

Route::group(['prefix' => 'types'], function () {
    Route::get('/', 'ApiTypeController@list');
    Route::post('/', 'ApiTypeController@add');
    Route::put('/{type}', 'ApiTypeController@update');
    Route::delete('/{type}', 'ApiTypeController@delete');

    Route::get('/{doctor}/payTypes', 'ApiDoctorController@payTypes');
    Route::get('/{doctor}/{payType}/appointmentTypes', 'ApiDoctorController@appointmentTypes');
});

Route::group(['prefix' => 'openings'], function () {
    Route::get('/', 'ApiOpeningController@listOpenings');
    Route::post('/', 'ApiOpeningController@addOpening');
    Route::delete('/delete/{opening}', 'ApiOpeningController@delete');
    Route::put('/update/{opening}', 'ApiOpeningController@updateOpening');
    Route::put('/exceptDate/{opening}', 'ApiOpeningController@addException');
    Route::get('/exceptions', 'ApiOpeningController@getAllExceptions');
    Route::delete('/exceptions/{exception}', 'ApiOpeningController@deleteException');
    Route::post('/isEventInOpenings', 'ApiOpeningController@isEventInOpenings');
});

Route::get('/test', function() {
    var_dump(\Illuminate\Support\Facades\Auth::user());
});
