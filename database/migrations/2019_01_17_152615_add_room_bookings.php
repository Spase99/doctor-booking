<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoomBookings extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('room_id');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->unsignedInteger('doctor_id');
            $table->unsignedInteger('type_id')->nullable();
            $table->boolean('weekly_repeat');
            $table->date('repeat_until')->nullable();
            $table->unsignedInteger('booked_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('booking_exceptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id');
            $table->date('exception_date');
            $table->timestamps();
        });

        Schema::table('room_bookings', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('booked_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });

        Schema::table('booking_exceptions', function (Blueprint $table) {
            $table->foreign('booking_id')->references('id')->on('room_bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('room_bookings');
        Schema::drop('booking_exceptions');
    }
}
