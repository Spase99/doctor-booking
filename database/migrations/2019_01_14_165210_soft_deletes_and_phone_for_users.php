<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SoftDeletesAndPhoneForUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone');
            $table->string('url_slug')->unique()->nullable();
            $table->boolean('can_turn_in')->default(true);
            $table->string('google_account')->unique()->nullable();
            $table->string('google_calendar_id')->unique()->nullable();
            $table->string('notification_channel_id')->unique()->nullable();
            $table->string('sync_token')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('deleted_at');
        });
    }
}
