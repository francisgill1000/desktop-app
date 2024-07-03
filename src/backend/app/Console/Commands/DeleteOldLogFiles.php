<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


use Illuminate\Support\Facades\File;

class DeleteOldLogFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:files-delete-old-log-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete .log and .txt files older than 7 days from the specified path';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = storage_path() . "/app"; //"/mytime2cloud/backend/storage/app";
        $this->deleteAttendanceLogFiles($path);

        $path = storage_path() . "/kernal_logs"; //"/mytime2cloud/backend/storage/app";
        $this->deleteAttendanceLogFiles($path);

        $path = storage_path() . "/logs"; //"/mytime2cloud/backend/storage/app";
        $this->deleteAttendanceLogFiles($path);

        $path = storage_path() . "/dev_logs"; //"/mytime2cloud/backend/storage/app";
        $this->deleteAttendanceLogFiles($path);

        $path = storage_path() . "/camera"; //"/mytime2cloud/backend/storage/app";
        $this->deleteAttendanceLogFiles($path);

        $path = "/var/www/mytime2cloud/camera-xml-logs"; //"/mytime2cloud/backend/storage/app";
        $this->deleteAttendanceLogFiles($path);
    }

    public function deleteAttendanceLogFiles($path)
    {
        //$path = storage_path() . "/app"; //"/mytime2cloud/backend/storage/app";

        if (!File::exists($path)) {
            echo "The specified path does not exist.";
            return 1;
        }

        //$files = File::files($path);
        $files = File::allFiles($path);

        echo $path . " - Files count - " . count($files);

        $now = time();
        $days30 = 30 * 24 * 60 * 60; //30Days days



        foreach ($files as $file) {
            $extension = $file->getExtension();
            if (in_array($extension, ['log', 'txt', 'csv']) && ($now - $file->getMTime() >= $days30)) {
                File::delete($file);
                $this->info("Deleted: {$file->getFilename()}");
            }
        }

        $this->info('Old files deletion process completed.');
        return 0;
    }
}
