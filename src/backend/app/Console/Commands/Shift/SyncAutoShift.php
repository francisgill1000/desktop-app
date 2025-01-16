<?php

namespace App\Console\Commands\Shift;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as Logger;

class SyncAutoShift extends Command
{
    /**
     * The name and signature of the console command sync_auto_shift.
     *
     * @var string
     */
    protected $signature = 'task:sync_auto_shift {company_id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Auto Shift';
    public function handle()
    {
        $localIp = gethostbyname(gethostname());
        $port = 8000;
        $endpoint = "http://$localIp:$port/api/render_logs";

        $id = $this->argument("company_id");
        $date = $this->argument("date");

        $employeeIds = Employee::where("company_id", $id)
            ->whereHas("schedule", fn($q) => $q->where("isAutoShift", true))
            ->pluck("system_user_id");

        try {
            // Log the start of the process
            Logger::channel('custom')->info('Starting SyncAutoShiftNew process', [
                'company_id' => $id,
                'date' => $date,
                'endpoint' => $endpoint,
            ]);

            // Chunk the employee IDs array into batches of 20
            $employeeIds->chunk(5)->each(function ($chunk) use ($id, $date, $endpoint) {
                $params = [
                    'date' => '',
                    'UserID' => '',
                    'updated_by' => 26,
                    'company_ids' => [$id],
                    'manual_entry' => true,
                    'reason' => '',
                    'employee_ids' => $chunk->toArray(),
                    'dates' => [$date, $date],
                    'shift_type_id' => 2,
                    'company_id' => $id,
                ];

                try {
                    // Log the parameters for the current chunk
                    Logger::channel('custom')->info('Sending request to endpoint', [
                        'chunk' => $chunk->toArray(),
                        'params' => $params,
                    ]);

                    // Call the endpoint using Http facade
                    $response = Http::withoutVerifying()->get($endpoint, $params);

                    // Log the response
                    if ($response->successful()) {
                        Logger::channel('custom')->info('Request successful', [
                            'chunk' => $chunk->toArray(),
                            'response' => $response->json(),
                        ]);
                        echo "Success: Processed chunk\n";
                    } else {
                        Logger::channel('custom')->error('Request failed', [
                            'chunk' => $chunk->toArray(),
                            'status' => $response->status(),
                            'error_body' => $response->body(),
                        ]);
                        echo "Error: {$response->status()} - {$response->body()}\n";
                    }
                } catch (\Exception $e) {
                    // Log any unexpected errors during the request
                    Logger::channel('custom')->critical('Unexpected error during request', [
                        'chunk' => $chunk->toArray(),
                        'exception_message' => $e->getMessage(),
                    ]);
                    echo "Critical Error: {$e->getMessage()}\n";
                }
            });

            // Log the completion of the process
            Logger::channel('custom')->info('SyncAutoShiftNew process completed successfully', [
                'company_id' => $id,
                'date' => $date,
            ]);
        } catch (\Exception $e) {
            // Log any unexpected errors in the overall process
            Logger::channel('custom')->critical('Unexpected error in SyncAutoShiftNew process', [
                'company_id' => $id,
                'date' => $date,
                'exception_message' => $e->getMessage(),
            ]);
            echo "Critical Error in Process: {$e->getMessage()}\n";
        }
    }
}
