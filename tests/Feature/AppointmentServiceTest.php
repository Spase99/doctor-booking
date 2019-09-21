<?php

namespace Tests\Feature;

use App\Booking;
use App\Doctor;
use App\Exceptions\OverbookingException;
use App\Room;
use App\Services\AppointmentService;
use App\Services\BookingService;
use App\Type;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppointmentServiceTest extends TestCase {
    use DatabaseTransactions;

    /**
     * @var AppointmentService
     */
    private $appointmentService;

    private $reception;
    private $validPatientData = [
        'first_name' => 'Maxx',
        'last_name' => 'Mustermann',
        'phone' => '555-54534',
        'email' => 'max.mustermann@example.org',
    ];

    private $doc;
    private $type;

    protected function setUp(): void {
        parent::setUp();
        $this->appointmentService = new AppointmentService();

        $this->doc = factory(User::class)->create();
        $this->doc->assignRole('doctor');

        $this->reception = factory(User::class)->create();
        $this->reception->assignRole('reception');

        $this->type = Type::first();
        $room = factory(Room::class)->create();

        $bookingService = new BookingService();
        $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => true,
            "room_id" => $room->id, "doctor_id" => $this->doc->id, 'type_id' => $this->type->id];
        $bookingService->add($booking, $this->doc);
    }

    public function testThatAddingAnAppointmentWorks() {
        $patient = $this->validPatientData;
        $patient['type'] = $this->type->id;
        $patient['start'] = '2019-01-01 14:00';
        $patient['end'] = '2019-01-01 14:30';

        $this->assertDatabaseMissing('appointments', ["start"=>"2019-01-01 14:00"]);

        $appointment = $this->appointmentService->add($patient, $this->doc);

        $this->assertDatabaseHas('appointments', ["start"=>"2019-01-01 14:00"]);

    }

    public function testThatTwoAppointmentsCannotBeCreatedAtTheSameTime() {
        $patient = $this->validPatientData;
        $patient['type'] = $this->type->id;
        $patient['start'] = '2019-01-01 14:00';
        $patient['end'] = '2019-01-01 14:30';

        $this->assertDatabaseMissing('appointments', ["start"=>"2019-01-01 14:00"]);

        $appointment = $this->appointmentService->add($patient, $this->doc);

        $this->assertDatabaseHas('appointments', ["start"=>"2019-01-01 14:00"]);

        try {
            $appointment2 = $this->appointmentService->add($patient, $this->doc);
            $this->fail("Appointments should not be able to be created at the same time for the same doctor.");
        } catch (OverbookingException $exception) {
            ;
        }

    }

    public function testThatTwoAppointmentsCannotOverlap() {
        $patient = $this->validPatientData;
        $patient['type'] = $this->type->id;
        $patient['start'] = '2019-01-01 14:00';
        $patient['end'] = '2019-01-01 14:30';

        $this->assertDatabaseMissing('appointments', ["start"=>"2019-01-01 14:00"]);

        $appointment = $this->appointmentService->add($patient, $this->doc);
        $this->assertDatabaseHas('appointments', ["start"=>"2019-01-01 14:00"]);

        $patient2 = $patient;
        $patient2['start'] = '2019-01-01 14:10';
        $patient2['end'] = '2019-01-01 14:40';

        try {
            $appointment2 = $this->appointmentService->add($patient2, $this->doc);

            $this->fail("Appointments should not be able to overlap for the same doctor.");
        } catch (OverbookingException $exception) {
            ;
        }
    }



}