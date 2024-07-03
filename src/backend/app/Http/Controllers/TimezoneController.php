<?php

namespace App\Http\Controllers;

use App\Http\Requests\Timezone\StoreRequest;
use App\Http\Requests\Timezone\UpdateRequest;
use App\Models\Timezone;
use App\Models\TimezoneDefaultJson;
use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    public function dropdownList()
    {
        $model = Timezone::query();
        $model->where('company_id', request('company_id'));
        $model->when(request()->filled('branch_id'), fn ($q) => $q->where('branch_id', request('branch_id')));
        $model->orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc");
        return $model->get(["id","timezone_name as name"]);
    }

    public function timezonesList(Request $request)
    {
        return Timezone::where('company_id', $request->company_id)->get(['id', 'timezone_name', 'timezone_id']);
    }
    public function index(Request $request)
    {
        $model = Timezone::query();
        $model->where('company_id', $request->company_id);
        $model->where("is_default", false);
        $model->when($request->branch_id, fn ($q) => $q->where("branch_id", $request->branch_id));
        $model->with(["employee_device", "branch"]);
        $model->orderBy("timezone_id", "asc");
        return $model->paginate($request->per_page ?? 100);
    }

    public function getTimezoneJson(Request $request)
    {
        return Timezone::where('company_id', $request->company_id)->pluck("json");
    }
    public function getNextAvaialbleTimezoneid($request)
    {

        $existingTimezoneIdsArray = Timezone::where("company_id", $request->company_id)->pluck("timezone_id");

        $allTimezoneIds = [];
        $aleadyExist = [];

        $nextAvaialbe_id = '';
        for ($i = 2; $i < 64; $i++) {
            $allTimezoneIds[] = $i;
        }
        foreach ($existingTimezoneIdsArray as  $value) {
            $aleadyExist[] = $value;
        }


        $elements_not_in_array = array_diff($allTimezoneIds, $aleadyExist);



        return $elements_not_in_array;
    }
    public function store(StoreRequest $request)
    {

        $data = $request->validated();
        $availalbeTimezoneIds = $this->getNextAvaialbleTimezoneid($request);


        $firstValue = reset($availalbeTimezoneIds);


        if (count($availalbeTimezoneIds)) {
            $data["timezone_id"] = $firstValue;
        } else {
            return $this->response('Timezone limit reached', null, false);
        }





        $data["scheduled_days"] = $this->processSchedule($data["scheduled_days"], false);
        $data["json"] = $this->processJson($request->timezone_id, $data["interval"], false);

        try {
            $record = Timezone::create($data);
            return $this->response('Timezone Successfully created.', $record, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(Timezone $timezone)
    {
        return $timezone->find();
    }
    public function getNewJsonIntervaldata(Request $request)
    {


        $intervals_raw_data = $request->intervals_raw_data;
        $input_time_slots = $request->input_time_slots;
        $inerval_array = [];
        $intervals_raw_data = json_decode($intervals_raw_data);
        $counter = 1;
        foreach ($intervals_raw_data as $value) {
            list($day, $hour) = explode('-', $value);


            $open_time = $input_time_slots[$hour];
            $newtimestamp = strtotime(date('Y-m-d ' . $open_time . ':00 ') . '+ 30 minute');

            $close_time = date('H:i', $newtimestamp);
            // $test['interval'] = ["begin" => $open_time, "end" => $close_time];
            $inerval_array[$day]['interval' . $counter] =   ["begin" => $open_time, "end" => $close_time];
            $counter++;
        }
        $final_array = [];


        for ($i = 0; $i <= 6; $i++) {
            if (isset($inerval_array[$i]))
                $final_array[] = $inerval_array[$i];
            else
                $final_array[] = [];
        }

        return $final_array;
    }
    public function update(UpdateRequest $request, Timezone $timezone)
    {
        $data = $request->validated();

        $final_array = $this->getNewJsonIntervaldata($request);

        ///---------------------------overiding interval
        $data["interval"] = $final_array;


        $data["scheduled_days"] = $this->processSchedule($data["scheduled_days"], false);
        $data["json"] = $this->processJson($request->timezone_id, $data["interval"], false);




        try {

            $record = $timezone->update($data);

            if ($record) {
                return $this->response('Timezone Successfully updated.', $record, true);
            } else {
                return $this->response('Timezone cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function update_old(UpdateRequest $request, Timezone $timezone)
    {
        $data = $request->validated();
        $data["scheduled_days"] = $this->processSchedule($data["scheduled_days"], false);
        $data["json"] = $this->processJson($request->timezone_id, $data["interval"], false);

        try {

            $record = $timezone->update($data);

            if ($record) {
                return $this->response('Timezone Successfully updated.', $record, true);
            } else {
                return $this->response('Timezone cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(Timezone $timezone)
    {
        try {

            $record = $timezone->delete();

            if ($record) {
                return $this->response('Timezone Successfully deleted.', $record, true);
            } else {
                return $this->response('Timezone cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function processIntervals($intervals, $isDefault)
    {
        $arr = [];

        foreach ($intervals as $key => $interval) {
            $arr[] = [
                "dayWeek" => $key,
                "timeSegmentList" => $this->processTimeFrames($interval, $isDefault),
            ];
        }
        return $arr;
    }
    public function processTimeFrames($interval, $isDefault = false)
    {
        $arr = [];

        for ($i = 1; $i <= 48; $i++) {
            if (isset($interval['interval' . $i]) && count($interval['interval' . $i]) > 0 && !$isDefault) {
                $arr[] = $interval['interval' . $i];
            } else {
                $arr[] = ["begin" => "00:00", "end" => "00:00"];
            }
        }
        return $arr;
    }
    public function processTimeFrames_old($interval, $isDefault = false)
    {
        $arr = [];

        for ($i = 1; $i <= 8; $i++) {
            if (isset($interval['interval' . $i]) && count($interval['interval' . $i]) > 0 && !$isDefault) {
                $arr[] = $interval['interval' . $i];
            } else {
                $arr[] = ["begin" => "00:00", "end" => "00:00"];
            }
        }
        return $arr;
    }
    public function storeTimezoneDefaultJson()
    {
        TimezoneDefaultJson::truncate();

        foreach (range(1, 64) as $iteration) {
            TimezoneDefaultJson::create([
                "index" => $iteration,
                "dayTimeList" => $this->dayTimeListArr(),
            ]);
        }
        return TimezoneDefaultJson::count();
    }

    public function GetTimezoneDefaultJson()
    {

        return TimezoneDefaultJson::get(['index', 'dayTimeList']);
    }

    public function processSchedule($schedules, $isDefault)
    {
        $arr = [];
        foreach ($schedules as $key => $d) {
            $arr[] = [
                "day" => $d["day"],
                "isScheduled" => $isDefault ? false : $d["isScheduled"],
                "dayWeek" => $key,
            ];
        }
        return $arr;
    }
    public function processJson($timezone_id, $interval, $isDefault)
    {
        return [
            "index" => $timezone_id,
            "dayTimeList" => $this->processIntervals($interval, $isDefault),
        ];
    }
    public function search(Request $request, $key)
    {
        return Timezone::where('company_id', $request->company_id)
            ->where("is_default", false)
            ->when($request->filled('filter_template_id'), function ($q) use ($request, $key) {
                $q->where('timezone_id', 'like', "$key%");
            })
            ->when($request->filled('filter_template_name'), function ($q) use ($request, $key) {
                $q->where('timezone_name', 'like', "$key%");
            })
            ->paginate($request->per_page ?? 100);
    }

    public function dayTimeListArr()
    {
        return [
            [
                "dayWeek" => 0,
                "timeSegmentList" => [
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ]
                ]
            ],
            [
                "dayWeek" => 1,
                "timeSegmentList" => [
                    [
                        "begin" => "14:22",
                        "end" => "14:22"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ]
                ]
            ],
            [
                "dayWeek" => 2,
                "timeSegmentList" => [
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ]
                ]
            ],
            [
                "dayWeek" => 3,
                "timeSegmentList" => [
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ]
                ]
            ],
            [
                "dayWeek" => 4,
                "timeSegmentList" => [
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ]
                ]
            ],
            [
                "dayWeek" => 5,
                "timeSegmentList" => [
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ]
                ]
            ],
            [
                "dayWeek" => 6,
                "timeSegmentList" => [
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ]
                ]
            ]
        ];
    }
}
