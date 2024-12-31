<?php

namespace App\Console\Commands\CloudToLocal;

use App\Models\Employee as ModelEmployee;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Http;


class Employee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloud-to-local:employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $company_id_from_cloud = $this->ask("company_id_from_cloud", 0);
        $company_id_from_local = $this->ask("company_id_from_local", 1);


        // Define the endpoint URL
        $url = 'https://backend.mytime2cloud.com/api/employeev1';

        // Make the GET request with query parameters
        $response = Http::withOptions([
            'verify' => false, // Disables SSL verification
        ])->get($url, [
            'per_page' => 1000,
            'company_id' => $company_id_from_cloud,
        ]);

        // Check if the request was successful (status code 200)
        if ($response->successful()) {

            ModelEmployee::truncate();

            // Decode the JSON response to an array or object
            $json = $response->json();

            $chunks = array_chunk($json["data"] ?? [], 10);

            foreach ($chunks as $key => $data) {
                $payload = [];
                foreach ($data as $row) {

                    $payload[] = [

                        "id" => $row["id"],
                        "title" => $row["title"],
                        "first_name" => $row["first_name"],
                        "last_name" => $row["last_name"],
                        "display_name" => $row["display_name"],
                        "profile_picture" => $row["profile_picture_raw"],

                        "phone_number" => $row["phone_number"],
                        "whatsapp_number" => $row["whatsapp_number"],

                        "employee_id" => $row["employee_id"],
                        "system_user_id" => $row["system_user_id"],

                        "joining_date" => $row["joining_date"],

                        "designation_id" => $row["designation_id"] ?? 0,
                        "department_id" => $row["department_id"] ?? 0,
                        "sub_department_id" => $row["sub_department_id"] ?? 0,

                        "status" => $row["status"] ?? 0,
                        "timezone_id" => $row["timezone_id"] ?? 0,
                        "branch_id" => $row["branch_id"],
                        "company_id" => $company_id_from_local,
                    ];
                }

                ModelEmployee::insert($payload);

                $this->info("Data has need insterted for " . $key + 1 .  " Batch");

                $this->info(count($payload));
            }

            while (true) {}
        } else {
            // Handle the error if the request fails
            $this->error('Failed to fetch data from the API');
        }
    }
}
