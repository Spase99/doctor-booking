<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Mail\ReminderEmail;
use App\Services\AppointmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class sendAppointmentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:sendReminder {days=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a reminder email for customers that have an appointment soon.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(AppointmentService $appointmentService)
    {
        $upcomingAppointments = $appointmentService->getAppointmentInDays( $this->argument('days'), true);

        $logMessage = "Sent Appointment Reminders.\n";
        $logMessage .= "Results: ".$upcomingAppointments->count()."\n";

        foreach ($upcomingAppointments as $appointment) {
            $logMessage .= $appointment->email."\n";
            if($appointment->email) {
                Mail::to($appointment->email)->send(new ReminderEmail($appointment));
            }

        }

        Log::info($logMessage);

        return 0;
    }
}
