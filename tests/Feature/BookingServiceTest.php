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

class BookingServiceTest extends TestCase {
    use DatabaseTransactions;

    /** @var BookingService */
    private $bookingService;

    private $reception;

    private $docA;
    private $docB;
    private $docC;

    private $roomA;
    private $roomB;
    private $roomC;

    private $type;

    protected function setUp(): void {
        parent::setUp();
        $this->bookingService = new BookingService();

        $this->docA = factory(User::class)->create();
        $this->docA->assignRole('doctor');

        $this->docB = factory(User::class)->create();
        $this->docB->assignRole('doctor');
        $this->docC = factory(User::class)->create();
        $this->docC->assignRole('doctor');

        $this->roomA = factory(Room::class)->create();
        $this->roomB = factory(Room::class)->create();
        $this->roomC = factory(Room::class)->create();

        $this->reception = factory(User::class)->create();
        $this->reception->assignRole('reception');

        $this->type = Type::first();
    }

    public function testThatCloseRepeatingEventsWork() {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 8:00"]);

        try {
            $booking = ["start" => "2019-01-01 8:00", "end" => "2019-01-01 11:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => 1];
            $this->bookingService->add($booking, $this->docA);
            $this->assertDatabaseHas('room_bookings', ["start" => "2019-01-01 8:00"]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        try {
            $booking2 = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => 1];
            $this->bookingService->add($booking2, $this->docB);
            $this->assertDatabaseHas('room_bookings', ["start" => $booking2["start"]]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        try {
            $booking3 = ["start" => "2019-01-01 11:15", "end" => "2019-01-01 13:45", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => 1];
            $this->bookingService->add($booking3, $this->docB);
            $this->assertDatabaseHas('room_bookings', ["start" => $booking3["start"]]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }
    }

    public function testThatRepeatingOverlappingEventsMustFail()
    {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 14:00"]);

        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking, $this->docA);
            $this->assertDatabaseHas('room_bookings', ["start" => "2019-01-01 14:00"]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        // there is a collision starting one week after
        try {
            $booking2 = ["start" => "2019-01-08 15:00", "end" => "2019-01-08 16:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking2, $this->docB);
            $this->fail("This should be an overbooking (booking 2)");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking2["start"]]);
        }
    }

    /**
     * Create an event from 14:00-17:00 on Tuesday (01.01.) and repeat it for 2 weeks until (inclusive) 15.01.
     * A follow up event must fail if it starts on 15.01.
     */
    public function testThatFollowUpEventsStartingToEarlyFail() {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 14:00"]);

        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => $this->type->id];
            $bookingRecord = $this->bookingService->add($booking, $this->docA);
            $this->bookingService->setEndDate($bookingRecord, "2019-01-15");
            $this->assertDatabaseHas('room_bookings', ["start" => "2019-01-01 14:00"]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        try {
            $booking2 = ["start" => "2019-01-15 13:00", "end" => "2019-01-15 16:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking2, $this->docB);
            $this->fail("This should be an overbooking.");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking2["start"]]);
        }
    }

    public function testThatFollowUpEventsShouldWork()
    {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 14:00"]);

        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => $this->type->id];
            $bookingRecord = $this->bookingService->add($booking, $this->docA);
            $this->bookingService->setEndDate($bookingRecord, "2019-01-15");
            $this->assertDatabaseHas('room_bookings', ["start" => "2019-01-01 14:00"]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        try {
            $booking2 = ["start" => "2019-01-22 14:00", "end" => "2019-01-22 16:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking2, $this->docB);
            $this->assertDatabaseHas('room_bookings', ["start" => $booking2["start"]]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        try {
            $booking3 = ["start" => "2019-01-22 16:15", "end" => "2019-01-22 18:00", "repeat" => true,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking3, $this->docA);
            $this->assertDatabaseHas('room_bookings', ["start" => $booking3["start"]]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }
    }

    public function testThatSingleEventMustNotOverlapWithRepeatingEvents()
    {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 14:00"]);

        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking, $this->docA);
            $this->assertDatabaseHas('room_bookings', ["start" => "2019-01-01 14:00"]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        // contained in other event
        try {
            $booking2 = ["start" => "2019-01-01 15:00", "end" => "2019-01-01 16:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking2, $this->docB);
            $this->fail("This should be an overbooking (booking 2)");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking2["start"]]);
        }

        // contains another event
        try {
            $booking3 = ["start" => "2019-01-01 12:00", "end" => "2019-01-01 18:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking3, $this->docB);
            $this->fail("This should be an overbooking (booking 3)");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking3["start"]]);
        }

        // starts while other event is still ongoing
        try {
            $booking4 = ["start" => "2019-01-01 16:00", "end" => "2019-01-01 18:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking4, $this->docB);
            $this->fail("This should be an overbooking (booking 4)");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking4["start"]]);
        }

        // ends when other event already started
        try {
            $booking5 = ["start" => "2019-01-01 12:00", "end" => "2019-01-01 15:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking5, $this->docB);
            $this->fail("This should be an overbooking (booking 5)");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking5["start"]]);
        }

        // ignore 15 minute break (only when different doctor) at the beginning
        try {
            $booking6 = ["start" => "2019-01-01 17:10", "end" => "2019-01-01 19:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking6, $this->docB);
            $this->fail("This should be an overbooking (booking 6)");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking6["start"]]);
        }
    }

    public function testThatIgnoring15MinutesBreakAtTheEndFails() {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 14:00"]);

        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking, $this->docA);
            $this->assertDatabaseHas('room_bookings', ["start" => "2019-01-01 14:00"]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        // ignore 15 minute break (only when different doctor) at the end
        try {
            $booking7 = ["start" => "2019-01-01 10:00", "end" => "2019-01-01 14:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking7, $this->docB);
            $this->fail("This should be an overbooking (booking 7)");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking7["start"]]);
        }
    }

    public function testThatBookingsNeedEssentialInformation() {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 14:30"]);

        $booking = ["start"=>"2019-01-01 14:30", "repeat" => false,
            "room_id" => $this->roomA->id, "doctor_id"=> $this->docB->id, 'type_id' => $this->type->id];

        try {
            $this->bookingService->add($booking, $this->docB);
        } catch (\Exception $exception) {
            $this->assertDatabaseMissing('room_bookings', ["start" => "2019-01-01 14:30"]);
        }

        $booking['end'] = "2019-01-01 15:00";
        $this->bookingService->add($booking, $this->docC);
        $this->assertDatabaseHas('room_bookings', ["start"=>"2019-01-01 14:30"]);
    }

    public function testThatRepeatingEventShowsOnlyDatesAfterInitialBooking() {
        $booking = [
            'room_id' => $this->roomA->id,
            'start' => '2019-04-27 09:00:00',
            'end' => '2019-04-27 19:00:00',
            'doctor_id' => $this->docA->id,
            'repeat_until' => '2019-07-20',
            'weekly_repeat' => true,
            'type_id' => $this->type->id
        ];

        $bookingRecord = $this->bookingService->add($booking, $this->docA);

        $month = "2019-04";

        $appointmentService = new AppointmentService();
        $appoinments = $appointmentService->getAppointments($this->docA, $this->type, $month);

        $dates = array_keys($appoinments);

        foreach ($dates as $date) {
            $this->assertGreaterThanOrEqual($bookingRecord->start->startOfDay(), (new Carbon($date))->startOfDay());
        }
    }

    public function testThatEventsMayOverlapInDifferentRooms() {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 14:00"]);

        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking, $this->docA);
            $this->assertDatabaseHas('room_bookings', ["start" => "2019-01-01 14:00"]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => false,
                "room_id" => $this->roomB->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking, $this->docB);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }
    }

    public function testThatTwoSingleEventsMustNotOverlap()
    {
        $this->assertDatabaseMissing('room_bookings', ["start"=>"2019-01-01 14:00"]);

        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking, $this->docA);
            $this->assertDatabaseHas('room_bookings', ["start" => "2019-01-01 14:00"]);
        } catch (OverbookingException $e) {
            $this->fail("Non-conflicting event could not be created.");
        }

        // there is a collision starting one week after
        try {
            $booking2 = ["start" => "2019-01-01 16:59", "end" => "2019-01-01 18:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docB->id, 'type_id' => $this->type->id];
            $this->bookingService->add($booking2, $this->docB);
            $this->fail("This should be an overbooking (booking 2)");
        } catch (OverbookingException $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking2["start"]]);
        }
    }

    public function testThatDoctorsCannotCreateBookingsWithoutType() {
        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => null];
            $this->bookingService->add($booking, $this->docA);
            $this->fail("Booking with no type should not be able to be created by doctors.");
        } catch (\Exception $e) {
            $this->assertDatabaseMissing('room_bookings', ["start" => $booking["start"]]);
        }
    }

    public function testThatReceptionCanCreateBookingsWithoutType() {
        try {
            $booking = ["start" => "2019-01-01 14:00", "end" => "2019-01-01 17:00", "repeat" => false,
                "room_id" => $this->roomA->id, "doctor_id" => $this->docA->id, 'type_id' => null];
            $this->bookingService->add($booking, $this->reception);
            $this->assertDatabaseHas('room_bookings', ["start" => $booking["start"]]);
        } catch (\Exception $e) {
            $this->fail("Booking with no type should be able to be created by reception.");

        }
    }

}