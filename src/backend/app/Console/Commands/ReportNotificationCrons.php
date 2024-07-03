<?php

namespace App\Console\Commands;

use App\Http\Controllers\WhatsappController;
use App\Mail\ReportNotificationMail;
use App\Models\report_notification_logs;
use App\Models\ReportNotification;
use App\Models\ReportNotificationLogs;
use Illuminate\Support\Facades\Mail;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;


class ReportNotificationCrons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:report_notification_crons {id} {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report Notification Crons';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument("id");
        $company_id = $this->argument("company_id");


        $script_name = "ReportNotificationCrons";

        $date = date("Y-m-d H:i:s");

        try {

            $model = ReportNotification::where("type", "automation")->with(["managers", "company.company_mail_content"])->where("id", $id)

                ->with("managers", function ($query) use ($company_id) {
                    $query->where("company_id", $company_id);
                })->first();


            if (in_array("Email", $model->mediums ?? [])) {

                // if ($model->frequency == "Daily") {

                foreach ($model->managers as $key => $value) {


                    Mail::to($value->email)
                        ->send(new ReportNotificationMail($model, $value));


                    $data = ["company_id" => $value->company_id, "branch_id" => $value->branch_id, "notification_id" => $value->notification_id, "notification_manager_id" => $value->id, "email" => $value->email];



                    ReportNotificationLogs::create($data);
                }
            } else {
                echo "[" . $date . "] Cron: $script_name. No emails are configured";
            }

            //wahtsapp with attachments
            if (in_array("Whatsapp", $model->mediums ?? [])) {

                foreach ($model->managers as $key => $manager) {

                    if ($manager->whatsapp_number != '') {




                        $attachments = [];

                        foreach ($model->reports as $file) {

                            $file_path = "app/pdf/" . $model->company->id . "/" . $file;
                            if (file_exists(storage_path($file_path))) {

                                $attachments = [];
                                $attachments["media_url"] =  env('BASE_URL') . '/api/donwload_storage_file?file_name=' . urlencode($file_path);
                                //$attachments["media_url"] =  "https://backend.mytime2cloud.com/api/donwload_storage_file?file_name=app%2Fpdf%2F2%2Fdaily_missing.pdf";

                                $attachments["filename"] = $file;

                                //https://backend.mytime2cloud.com/api/donwload_storage_file?file_name=app%2Fpdf%2F2%2Fdaily_missing.pdf
                                //print_r($attachments);
                                //return $attachments;
                                (new WhatsappController())->sendWhatsappNotification($model->company, $file, $manager->whatsapp_number, $attachments);

                                $data = [
                                    "company_id" => $model->company->id,
                                    "branch_id" => $manager->branch_id,
                                    "notification_id" => $manager->notification_id,
                                    "notification_manager_id" => $manager->id,
                                    "whatsapp_number" => $manager->whatsapp_number
                                ];

                                ReportNotificationLogs::create($data);
                            }
                        } //for 


                        $body_content1 = "*Hello, {$manager->name}*\n\n";
                        $body_content1 .= "*Company:  {$model->company->name}*\n\n";
                        if (count($model->company->company_whatsapp_content))
                            $body_content1 .= $model->company->company_whatsapp_content[0]->content;

                        (new WhatsappController())->sendWhatsappNotification($model->company, $body_content1, $manager->whatsapp_number, $attachments);
                    }
                }
            }




            echo "[" . $date . "] Cron: $script_name. Report Notification Crons has been sent.\n";
            return;
        } catch (\Throwable $th) {

            echo $th;
            echo "[" . $date . "] Cron: $script_name. Error occured while inserting logs.\n";
            Logger::channel("custom")->error("Cron: $script_name. Error Details: $th");
            return;
        }
    }
}
