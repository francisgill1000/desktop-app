<?php

namespace App\Console\Commands\CloudToLocal;

use App\Models\CompanyBranch;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Http;


class Branch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloud-to-local:branch';

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
        $url = 'https://backend.mytime2cloud.com/api/branch';

        // Make the GET request with query parameters
        $response = Http::withOptions([
            'verify' => false, // Disables SSL verification
        ])->get($url, [
            'per_page' => 1000,
            'company_id' => $company_id_from_cloud,
        ]);

        $payload = [];

        // Check if the request was successful (status code 200)
        if ($response->successful()) {
            // Decode the JSON response to an array or object
            $json = $response->json();

            $data = $json["data"] ?? [];

            foreach ($data as $row) {
                $payload[] = [
                    "id" => $row["id"],
                    "branch_code" => $row["branch_code"],
                    "branch_name" => $row["branch_name"],
                    "address" => $row["address"],
                    "licence_issue_by_department" => $row["licence_issue_by_department"],
                    "licence_number" => $row["licence_number"],
                    "licence_expiry" => "2025-12-31",
                    "company_id" => $company_id_from_local,
                ];
            }
            CompanyBranch::truncate();

            CompanyBranch::insert($payload);

            $this->info("Data has need insterted");

            while (true) {
            }
        } else {
            // Handle the error if the request fails
            $this->error('Failed to fetch data from the API');
        }
    }
}
