<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpeningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('openings', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->boolean('is_opening')->default(true);
            $table->boolean('weekly_repeat');
            $table->date('repeat_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('opening_exceptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('opening_id');
            $table->date('exception_date');
            $table->timestamps();

            $table->foreign('opening_id')->references('id')->on('openings')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openings');
        Schema::drop('opening_exceptions');
    }
}
