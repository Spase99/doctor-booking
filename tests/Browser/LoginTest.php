<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

/*
 * Login Credentials used in this class are created with UsersTableSeeder.
 */

class LoginTest extends DuskTestCase {
    use DatabaseTransactions;

    // See: https://stackoverflow.com/questions/44906797/laravel-dusk-how-to-destroy-session-data-between-tests
    protected function setUp():void {
        parent::setUp();
        foreach (static::$browsers as $browser) {
            $browser->driver->manage()->deleteAllCookies();
        }
    }

    public function testLoginIsStartPageForBackend() {
        $this->browse(function (Browser $browser) {
            $browser->visit('/backend')
                ->assertSee('Login');
        });
    }

    public function testLoginWorksForAdministrator() {
        $email = 'admin@example.org';
        $password = 'secret';

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->visit('/backend')
                ->type('email', $email)
                ->type('password', $password)
                ->press("Login")
                ->assertSee("Administratoransicht");
        });
    }

    public function testLoginWorksForDoctors() {
        $email = 'arzt1@example.org';
        $password = 'secret';

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->visit('/backend')
                ->type('email', $email)
                ->type('password', $password)
                ->press("Login")
                ->assertSee("Arztansicht");
        });
    }
}
