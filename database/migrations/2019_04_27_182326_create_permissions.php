<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreatePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Permission::create(['name' => 'create bookings']);
        Permission::create(['name' => 'create repeating bookings']);
        Permission::create(['name' => 'book rooms for others']);
        Permission::create(['name' => 'list doctors']);
        Permission::create(['name' => 'add doctors']);
        Permission::create(['name' => 'update doctors']);
        Permission::create(['name' => 'delete doctors']);
        Permission::create(['name' => 'list rooms']);
        Permission::create(['name' => 'add rooms']);
        Permission::create(['name' => 'update rooms']);
        Permission::create(['name' => 'delete rooms']);
        Permission::create(['name' => 'list types']);
        Permission::create(['name' => 'add types']);
        Permission::create(['name' => 'delete types']);
        Permission::create(['name' => 'update types']);
        Permission::create(['name' => 'delete bookings']);
        Permission::create(['name' => 'add booking exceptions']);
        Permission::create(['name' => 'add own booking exceptions']);
        Permission::create(['name' => 'list booking exceptions']);
        Permission::create(['name' => 'delete booking exceptions']);
        Permission::create(['name' => 'delete own booking exceptions']);
        Permission::create(['name' => 'update booking exceptions']);
        Permission::create(['name' => 'update bookings']);
        Permission::create(['name' => 'update own bookings']);
        Permission::create(['name' => 'view bookings']);
        Permission::create(['name' => 'see booking names']);
        Permission::create(['name' => 'select bookings']);
        Permission::create(['name' => 'delete own bookings']);
        Permission::create(['name' => 'create overtime bookings']);

        $adminPermissions = ["create bookings", "create repeating bookings", 'list doctors', 'add doctors', 'update doctors',
            'delete doctors', 'list rooms', 'update rooms', 'delete rooms', 'list types', 'add types',
            'delete types', 'update types', 'delete bookings', 'add booking exceptions', 'delete booking exceptions',
            'update booking exceptions', 'update bookings', 'see booking names', 'select bookings'];
        $docPermissions = ["list booking exceptions", 'view bookings', "select bookings"];
        $bookingDocPermissions = ['create bookings', 'create repeating bookings',
            'list rooms', 'list types', 'delete own bookings', 'add own booking exceptions', 'delete own booking exceptions',
            'update booking exceptions', 'update own bookings', 'view bookings', 'select bookings'];
        $receptionPermissions = ["create bookings", "list doctors", "list rooms", "select bookings", 'see booking names',
            'delete own bookings', 'update own bookings', 'create overtime bookings'];

        Role::create(['name' => 'doctor'])->givePermissionTo($docPermissions);
        Role::create(['name' => 'bookingDoctor'])->givePermissionTo($bookingDocPermissions);
        Role::create(['name' => 'admin'])->givePermissionTo($adminPermissions);
        Role::create(['name' => 'reception'])->givePermissionTo($receptionPermissions);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function down()
    {
        // TODO: delete all permissions and roles
    }
}
