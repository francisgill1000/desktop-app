<?php

namespace Database\Seeders;

use App\Models\ShiftType;
use Illuminate\Database\Seeder;

class ShiftTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $shiftType = [
            [
                'name' => 'FILO',
                'slug' => 'no_shift',

            ],
            [
                'name' => 'Multi In/Out Shift',
                'slug' => 'multi_in_out_shift',

            ],
            [
                'name' => 'Auto Shift',
                'slug' => 'auto_shift',

            ],
            [
                'name' => 'Night Shift',
                'slug' => 'night_shift',

            ],
            [
                'name' => 'Split Shift',
                'slug' => 'split_shift',

            ],
            [
                'name' => 'Single Shift',
                'slug' => 'manual_shift',

            ],

        ];
        ShiftType::insert($shiftType);
    }
}
