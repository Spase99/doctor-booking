<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('duration');
            $table->text('pay_type');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->string('description')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('doctor_id');
            $table->unsignedInteger('booking_id')->nullable();
            $table->string('google_eventid')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            //$table->foreign('booking_id')->references('id')->on('room_bookings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('appointments');
        Schema::drop('patients');
        Schema::drop('types');
    }
}
