<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faqs = [
            [
                "question" => "How do I download a device log?",
                "answer" => "Click to attendance on top menu then navigate to device logs. 
select the filters if required.",
                "search_terms" => "download ,downloads ,device ,devices, log, logs"
            ],
            [
                "question" => "How to download the attendance report?	",
                "answer" => "Click to attendance on top menu then navigate to reports. 
select the filters if required then click to generate button. 
after you get the data you can download the report with your desired format",
                "search_terms" => "download ,downloads, attendance, attendances ,report, reports"
            ],
            [
                "question" => "How do I edit an employee's details?",
                "answer" => "To edit click to employees tab from top menu. then click to menu option for any specific employee to edit the details",
                "search_terms" => "employee, detail, details, edit, update, employees"
            ],
            [
                "question" => "How do I schedule employees?",
                "answer" => "Click to attendance on top menu then navigate to schedule .click to add schedule button of edit specific employee",
                "search_terms" => "schedule, schedules, employee, employees"
            ],
            [
                "question" => "How do I create a schedule?",
                "answer" => "Click to attendance on top menu then navigate to schedule .click to add schedule button of edit specific employee",
                "search_terms" => "schedule, schedules"
            ],
            [
                "question" => "How do I create a shift?",
                "answer" => "Click to attendance on top menu then navigate to shift. 
then create new shift by clicking on new button",
                "search_terms" => "shift,shifts"
            ],
            [
                "question" => "How do I create a new employee profile?",
                "answer" => "To create a new employee profile, navigate to the 'Employees' section, click 'Add Employee', and fill in the required details such as name, employee ID, and department.",
                "search_terms" => "employee, employees ,profile, profiles, account,accounts,register"
            ],
        ];

        Faq::truncate();

        Faq::insert($faqs);

        ld(Faq::get());
    }
}
