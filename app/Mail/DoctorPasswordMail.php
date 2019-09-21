<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DoctorPasswordMail extends Mailable {
    use Queueable, SerializesModels;
    public $doctor;
    public $plaintextPassword;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($doctor, $plaintextPassword) {
        $this->doctor = $doctor;
        $this->plaintextPassword = $plaintextPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->view('mails.initial_password');
    }
}
