<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Parking\StoreRequest;
use App\Http\Requests\Community\Parking\UpdateRequest;
use App\Models\Community\Parking;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Parking::orderBy('id', 'desc')->paginate(request("per_page") ?? 10);
    }

    public function store(StoreRequest $request)
    {
        try {

            $record = Parking::create($request->validated());

            if ($record) {
                return $this->response('Parking Successfully created.', $record, true);
            } else {
                return $this->response('Parking cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateRequest $request, Parking $Parking)
    {
        try {

            $record = $Parking->update($request->validated());

            if ($record) {
                return $this->response('Parking Successfully updated.', $record, true);
            } else {
                return $this->response('Parking cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Parking  $Parking
     * @return \Illuminate\Http\Response
     */

    public function destroy(Parking $Parking)
    {
        try {
            if ($Parking->delete()) {
                return $this->response('Parking successfully deleted.', null, true);
            } else {
                return $this->response('Parking cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
