<?php

namespace App\Console\Commands\CloudToLocal;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DeleteExtraEmployeeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee-images:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete extra profile pictures that do not exist in the Employee model';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Define the directory path for the images
        $imageDirectory = public_path('media/employee/profile_picture');

        // Get all the filenames in the directory
        $filesInDirectory = File::allFiles($imageDirectory);

        // Get all the profile pictures from the Employee model
        $employeePictures = Employee::pluck('profile_picture')->toArray();

        // Loop through each file in the directory
        foreach ($filesInDirectory as $file) {
            // Get the file name (e.g., 'employee_1.jpg')
            $filename = $file->getFilename();

            // Check if the file is not in the employee's profile_picture column
            if (!in_array($filename, $employeePictures)) {
                // If the image does not exist in the database, delete it
                File::delete($file);

                $this->info("Deleted: " . $filename);
            }
        }

        $this->info("Extra images have been deleted.");
        return 0;
    }
}
