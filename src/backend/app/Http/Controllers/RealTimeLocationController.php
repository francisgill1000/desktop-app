<?php

namespace App\Http\Controllers;

use App\Models\RealTimeLocation;
use Illuminate\Http\Request;
use App\Http\Requests\RealTimeLocation\StoreRequest;

class RealTimeLocationController extends Controller
{
    public function index(Request $request)
    {
        $model = RealTimeLocation::query();
        $model->where("company_id", $request->company_id);
        $model->where("UserID", $request->UserID);
        $model->where("date", $request->date ?? date("Y-m-d"));
        return $model->paginate($request->per_page ?? 100);
    }

    public function store(StoreRequest $request)
    {
        try {

            $model = RealTimeLocation::query();

            $model->where("company_id", $request->company_id);
            $model->where("device_id", $request->device_id);
            $model->where("UserID", $request->UserID);
            $model->where("latitude", $request->latitude);
            $model->where("longitude", $request->longitude);;

            if ($model->exists()) {
                return $this->response('Location already exist.', null, true);
            }
            $data = $request->validated();
            $data["date"] = date("Y-m-d");
            $data["datetime"] = date("Y-m-d H:i:s");
            $model->create($data);
            return $this->response('Realtime location added.', null, true);
        } catch (\Throwable $th) {
            return $this->response('Realtime location cannot add.', null, false);
        }
    }
}
