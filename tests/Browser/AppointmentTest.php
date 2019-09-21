<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AppointmentTest extends DuskTestCase {
    public function testVisittingADoctorsPageWorks() {
        $doc = User::where('url_slug', 'arzt-1')->first();

        $this->browse(function (Browser $browser) use ($doc) {
            $browser->visit('/doc/'.$doc->url_slug)
                ->assertSee($doc->name);
        });
    }
}
