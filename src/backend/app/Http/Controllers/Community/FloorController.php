<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Floor\StoreRequest;
use App\Http\Requests\Floor\UpdateRequest;
use App\Models\Community\Floor;

class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Floor::where("company_id",request("company_id") ?? 0)->paginate(request("per_page") ?? 10);
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
            $exists = Floor::where("company_id", $request->company_id)->where('floor_number', $request->floor_number)->exists();

            // Check if the floor number already exists
            if ($exists) {
                return $this->response('Floor already exists.', null, true);
            }

            $record = Floor::create($request->validated());

            if ($record) {
                return $this->response('Floor Successfully created.', $record, true);
            } else {
                return $this->response('Floor cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Floor  $floor
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Floor $floor)
    {
        $floor_number = $request->floor_number;

        // If the floor number is different from the updated value
        if ($floor->floor_number != $floor_number) {
            $exists = Floor::where("company_id", $request->company_id)->where('floor_number', $floor_number)->exists();

            // Check if the floor number already exists
            if ($exists) {
                return $this->response('Floor already exists.', null, true);
            }
        }

        try {
            // If the floor number is the same or it's unique, update the floor
            $record = $floor->update($request->validated());

            return $this->response('Floor successfully updated.', $record, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Floor  $floor
     * @return \Illuminate\Http\Response
     */

    public function destroy(Floor $floor)
    {
        try {
            if ($floor->delete()) {
                return $this->response('Floor successfully deleted.', null, true);
            } else {
                return $this->response('Floor cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
