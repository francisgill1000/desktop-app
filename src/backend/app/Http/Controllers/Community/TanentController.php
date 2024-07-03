<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Tanent\RegisterRequest;
use App\Http\Requests\Community\Tanent\StoreRequest;
use App\Http\Requests\Community\Tanent\UpdateRequest;
use App\Http\Requests\Community\Tanent\VehicleRequest;

use App\Models\Community\Tanent;
use App\Models\Community\Vehicle;
use Illuminate\Http\Request;

class TanentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Tanent::where("company_id", request("company_id"))->with(["vehicles", "members", "floor", "room"])->orderBy('id', 'desc')->paginate(request("per_page") ?? 10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateTanent(StoreRequest $request)
    {
        try {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $request->phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }

            return $this->response('Tanent Successfully created.', $request->validated(), true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function validateVehicle(VehicleRequest $request)
    {
        try {
            return $this->response('Vehicle Successfully created.', $request->validated(), true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function store(StoreRequest $request)
    {

        try {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $request->phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }

            $data = $request->validated();

            $data["full_name"] = "{$data["first_name"]} {$data["last_name"]}";


            $room_number = $request->room_number;
            $tanentId = Tanent::max('id') + 1;

            $data["system_user_id"] = "{$room_number}{$tanentId}";

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }

            $documents = [
                'passport_doc',
                'id_doc',
                'contract_doc',
                'ejari_doc',
                'license_doc',
                'others_doc'
            ];

            foreach ($documents as $document) {
                if ($request->hasFile($document)) {
                    $data[$document] = Tanent::ProcessDocument($request->file($document), "/community/$document");
                }
            }

            $record = Tanent::create($data);

            if ($record) {
                return $this->response('Tanent Successfully created.', $record, true);
            } else {
                return $this->response('Tanent cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function register(RegisterRequest $request)
    {
        try {

            $data = $request->validated();
            $room_number = $request->room_number;
            $tanentId = Tanent::max('id') + 1;
            $data["full_name"] = "{$data["first_name"]} {$data["last_name"]}";
            $data["system_user_id"] = "{$room_number}{$tanentId}";

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }

            $record = Tanent::create($data);

            if ($record) {
                return $this->response('Tanent Successfully created.', $record, true);
            } else {
                return $this->response('Tanent cannot create.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function storeVehicles(Request $request)
    {
        return Vehicle::insert($request->vehicles);
    }


    public function storeMultipleVehicles(Request $request,$id)
    {
        $model = Vehicle::query();
        $model->where("tanent_id",$id);
        $model->delete("tanent");
        return $model->insert($request->vehicles);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tanent  $Tanent
     * @return \Illuminate\Http\Response
     */

    public function validateUpdateTanent(UpdateRequest $request, $id)
    {
        $Tanent = Tanent::where("id", $id)->first();

        $phone_number = $request->phone_number;

        if ($Tanent->phone_number != $phone_number) {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }
        }

        return $this->response('Tanent successfully updated.', null, true);
    }


    public function tanentUpdate(UpdateRequest $request, $id)
    {
        $Tanent = Tanent::where("id", $id)->first();

        $phone_number = $request->phone_number;

        if ($Tanent->phone_number != $phone_number) {
            $exists = Tanent::where("company_id", $request->company_id)->where('phone_number', $phone_number)->exists();

            // Check if the Tanent number already exists
            if ($exists) {
                return $this->response('Tanent already exists.', null, true);
            }
        }

        try {

            $data = $request->validated();

            $data["full_name"] = "{$data["first_name"]} {$data["last_name"]}";

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }


            $documents = [
                'passport_doc',
                'id_doc',
                'contract_doc',
                'ejari_doc',
                'license_doc',
                'others_doc'
            ];

            foreach ($documents as $document) {
                if ($request->hasFile($document)) {
                    $data[$document] = Tanent::ProcessDocument($request->file($document), "/community/$document");
                }
            }

            $record = $Tanent->update($data);

            return $this->response('Tanent successfully updated.', $record, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tanent  $Tanent
     * @return \Illuminate\Http\Response
     */

    public function destroy(Tanent $Tanent)
    {
        try {
            if ($Tanent->delete()) {
                return $this->response('Tanent successfully deleted.', null, true);
            } else {
                return $this->response('Tanent cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
