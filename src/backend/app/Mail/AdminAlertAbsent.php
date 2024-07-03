<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminAlertAbsent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $date;
    public $absentEmployees;

    public function __construct($date, $absentEmployees)
    {
        $this->date = $date;
        $this->absentEmployees = $absentEmployees;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('System Notification: Absent Employees Update')
            ->with(["absentEmployees" => $this->absentEmployees, "date" => $this->date])
            ->markdown('emails.admin_alert_absent');
    }
}
