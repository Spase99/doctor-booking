<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

class AddBookingPermissionsToReceptionists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role = Role::findByName('reception');
        $role->givePermissionTo(['create repeating bookings', 'delete bookings', 'add booking exceptions', 'delete booking exceptions', 'update booking exceptions', 'update bookings', 'list types']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
