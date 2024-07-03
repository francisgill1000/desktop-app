<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Room\StoreRequest;
use App\Http\Requests\Community\Room\UpdateRequest;
use App\Models\Community\Room;
use App\Models\Company;
use App\Models\Community\Tanent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Room::where("company_id", request("company_id"))->with(["tanent", "floor", "room_category"])->paginate(request("per_page") ?? 10);
    }

    public function report()
    {
        return Room::where("company_id", request("company_id"))
            ->when(request()->filled("report_type"), function ($q) {
                if (request("report_type") == "Occupied") {
                    return $q->whereHas("tanent");
                }
                if (request("report_type") == "Available") {
                    return $q->whereDoesntHave("tanent");
                }

                if (request("report_type") == "Expire") {
                    return $q->whereHas("tanent", function ($query) {
                        $query->whereDate('end_date', '>=', date("Y-m-d"));
                        $query->whereDate('end_date', '<=', now()->addDays(30));
                    });
                }
            })
            ->with(["tanent", "floor", "room_category"])->paginate(request("per_page") ?? 10);
    }



    public function print(Request $request)
    {
        $model = Room::where("company_id", request("company_id"))
            ->when(request()->filled("report_type"), function ($q) {
                if (request("report_type") == "Occupied") {
                    return $q->whereHas("tanent");
                }
                if (request("report_type") == "Available") {
                    return $q->whereDoesntHave("tanent");
                }

                if (request("report_type") == "Expire") {
                    return $q->whereHas("tanent", function ($query) {
                        $query->whereDate('end_date', '>=', date("Y-m-d"));
                        $query->whereDate('end_date', '<=', now()->addDays(30));
                    });
                }
            })
            ->with(["tanent", "floor", "room_category"]);

        $data = $model->get()->toArray();

        if ($request->debug) return $data;

        $chunks = array_chunk($data, 10);

        return Pdf::setPaper('a4', 'landscape')->loadView('pdf.room_report.report', [
            "chunks" => $chunks,
            "company" => Company::whereId(request("company_id") ?? 0)->first(),
            "params" => $request->all(),

        ])->stream();
    }

    public function download(Request $request)
    {
        $model = Room::where("company_id", request("company_id"))
            ->when(request()->filled("report_type"), function ($q) {
                if (request("report_type") == "Occupied") {
                    return $q->whereHas("tanent");
                }
                if (request("report_type") == "Available") {
                    return $q->whereDoesntHave("tanent");
                }

                if (request("report_type") == "Expire") {
                    return $q->whereHas("tanent", function ($query) {
                        $query->whereDate('end_date', '>=', date("Y-m-d"));
                        $query->whereDate('end_date', '<=', now()->addDays(30));
                    });
                }
            })
            ->with(["tanent", "floor", "room_category"]);

        $data = $model->get()->toArray();

        if ($request->debug) return $data;

        $chunks = array_chunk($data, 10);

        return Pdf::setPaper('a4', 'landscape')->loadView('pdf.room_report.report', [
            "chunks" => $chunks,
            "company" => Company::whereId(request("company_id") ?? 0)->first(),
            "params" => $request->all(),

        ])->download();
    }

    public function getRoomsByFloorId()
    {
        return Room::where("company_id", request("company_id"))->where("floor_id", request("floor_id"))->get(["id", "room_number"]);
    }

    public function getTanentsAndMembersByRoomsId()
    {
        return Tanent::where("company_id", request("company_id"))->where("room_id", request("room_id"))->with("members")->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        try {
            $exists = Room::where("company_id", $request->company_id)->where('room_number', $request->room_number)->exists();

            // Check if the room number already exists
            if ($exists) {
                return $this->response('Room already exists.', null, true);
            }

            $record = Room::create($request->validated());

            if ($record) {
                return $this->response('Room Successfully created.', $record, true);
            } else {
                return $this->response('Room cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Room $room)
    {
        $newRoomNumber = $request->room_number;

        // If the room number is different from the updated value
        if ($room->room_number !== $newRoomNumber) {
            $exists = Room::where("company_id", $request->company_id)->where('room_number', $newRoomNumber)->exists();

            // Check if the room number already exists
            if ($exists) {
                return $this->response('Room already exists.', null, true);
            }
        }

        try {
            // If the room number is the same or it's unique, update the room
            $record = $room->update($request->validated());

            return $this->response('Room successfully updated.', $record, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */

    public function destroy(Room $room)
    {
        try {
            if ($room->delete()) {
                return $this->response('Room successfully deleted.', null, true);
            } else {
                return $this->response('Room cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
