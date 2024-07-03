<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReportNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $model;
    public $manager;
    public function __construct($model, $manager)
    {
        $this->model = $model;
        $this->manager = $manager;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject($this->model->subject);

        $company_id = $this->model->company_id;

        foreach ($this->model->reports as $file) {
            if (file_exists(storage_path("app/pdf/$company_id/$file")))
                $this->attach(storage_path("app/pdf/$company_id/$file"));
        }
        $body_content =  "Hi, Automated Email Reports. <br/>Thanks.";
        //return $this->view('emails.report')->with(["body" => $this->model->body]);
        if ($this->model->company->company_mail_content) {
            if ($this->model->company->company_mail_content[0]) {
                $body_content = $this->model->company->company_mail_content[0]->content;
            }
        }

        $body_content1  = "Hi " . $this->manager->name . '<br/>';
        $body_content1  =  $body_content1 . "<b>Company:  " . $this->model->company->name . '</b><br/>';
        $body_content  =  $body_content1 . $body_content;


        return $this->view('emails.report')->with(["body" =>  $body_content]);
    }
}
