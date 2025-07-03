<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReportNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public $model, public $manager, public $files) {}

    public function build()
    {
        $this->subject($this->model->subject);

        $companyId = $this->model->company_id;
        $branchId = $this->model->branch_id;
        $date = date("Y-m-d", strtotime("-1 day"));

        // Attach PDF files if they exist
        foreach ($this->files as $file) {
            $relativePath = "storage/pdf/{$date}/{$companyId}/summary_report_{$branchId}_{$file}.pdf";
            $fullPath = public_path($relativePath);

            if (file_exists($fullPath)) {
                $this->attach($fullPath);
            }
        }

        // Build email body
        $managerName = optional($this->manager)->name ?? 'Manager';
        $companyName = optional($this->model->company)->name ?? 'N/A';

        $bodyContent = "Hi {$managerName},<br/>";
        $bodyContent .= "<b>Company: {$companyName}</b><br/><br/>";
        $bodyContent .= "Hi, Automated Email Reports.<br/>Thanks.";

        return $this->view('emails.report')->with([
            'body' => $bodyContent
        ]);
    }
}
