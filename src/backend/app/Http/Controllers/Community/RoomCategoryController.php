<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\RoomCategory\StoreRequest;
use App\Http\Requests\Community\RoomCategory\UpdateRequest;

use App\Models\Community\RoomCategory;

class RoomCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RoomCategory::where("company_id",request("company_id") ?? 0)->paginate(request("per_page") ?? 10);
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
            $exists = RoomCategory::where("company_id", $request->company_id)->where('name', $request->name)->exists();

            // Check if the room number already exists
            if ($exists) {
                return $this->response('Room Category already exists.', null, true);
            }

            $record = RoomCategory::create($request->validated());

            if ($record) {
                return $this->response('Room Category Successfully created.', $record, true);
            } else {
                return $this->response('Room Category cannot create.', null, false);
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
    public function update(UpdateRequest $request, RoomCategory $RoomCategory)
    {
        $name = $request->name;

        // If the room number is different from the updated value
        if ($RoomCategory->name !== $name) {
            $exists = RoomCategory::where("company_id", $request->company_id)->where('name', $name)->exists();

            // Check if the RoomCategory number already exists
            if ($exists) {
                return $this->response('Room Category already exists.', null, true);
            }
        }

        try {
            // If the room number is the same or it's unique, update the room
            $record = $RoomCategory->update($request->validated());

            return $this->response('Room Category successfully updated.', $record, true);
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

    public function destroy(RoomCategory $RoomCategory)
    {
        try {
            if ($RoomCategory->delete()) {
                return $this->response('Room Category successfully deleted.', null, true);
            } else {
                return $this->response('Room Category cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
