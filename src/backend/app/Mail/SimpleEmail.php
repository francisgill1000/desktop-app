<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReportNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $model;

    public function __construct()
    {
        //$this->model = $model;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $this->subject($this->model->subject);

        // $company_id = $this->model->company_id;

        // foreach ($this->model->reports as $file) {
        //     $this->attach(storage_path("app/pdf/$company_id/$file"));
        // }

        // // return $this->view('emails.report')->with(["body" => $this->model->body]);
        // //return $this->view('emails.report')->with(["body" => "Hi, Attached reports for your reference. "]);

        // return $this->text('emails.report')->subject('Simple Email Subject1111')->attach(storage_path("app/payslips/8/8_1001_1_2023_payslip.pdf"))->with(["body" => "Hi, Attached reports for your reference. "]);

        // return $this->text('emails.report')
        //     ->subject('Simple Email Subject1111')->attach(storage_path("app/payslips/8/8_1001_1_2023_payslip.pdf"))
        //     ->with(["body" => $this->model->body]);

        return $this->text('emails.test')
            ->subject('Simple Email Subject1111111111')->attach(storage_path("app/payslips/8/8_1001_1_2023_payslip.pdf"))
            ->with([
                'body' => 'This is a plain text email message.',
            ]);
    }
}
