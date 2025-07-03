<?php

namespace App\Http\Controllers;

use App\Http\Requests\Holidays\StoreRequest;
use App\Http\Requests\Holidays\UpdateRequest;
use App\Models\Holidays;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidaysController extends Controller
{
    public function upcomingHoliday(Request $request)
    {
        $model = Holidays::query();
        $model->where('company_id', $request->company_id);
        $model->when($request->filled('branch_id'), fn($q) => $q->where('branch_id',  $request->branch_id));
        $model->where('year', $request->year ?? date("Y"));
        $model->orderByDesc("id");
        return $model->first();
    }
    public function getDefaultModelSettings($request)
    {
        $model = Holidays::query();
        $model->where('company_id', $request->company_id);
        $model->when($request->filled('branch_id'), fn($q) => $q->where('branch_id',  $request->branch_id));
        $model->where('year', $request->year ?? date("Y"));
        $model->when($request->filled('search_start_date'), fn($q) => $q->where('start_date', env('WILD_CARD') ?? 'ILIKE', "{$request->search_start_date}%"));
        $model->when($request->filled('search_end_date'), fn($q) => $q->where('end_date', env('WILD_CARD') ?? 'ILIKE', "{$request->search_end_date}%"));
        $model->when($request->filled('serach_total_days'), fn($q) => $q->where('total_days',  $request->serach_total_days));
        $model->with("branch");
        $model->orderByDesc("id");
        return $model;
    }

    public function index(Request $request)
    {

        return $this->getDefaultModelSettings($request)->paginate($request->per_page ?? 100);
    }

    function list(Request $request)
    {
        return $this->getDefaultModelSettings($request)->paginate($request->per_page ?? 100);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();

        try {
            // Database operations
            $record = Holidays::create($request->validated());

            DB::commit();
            if ($record) {

                return $this->response('Holidays Successfully created.', $record, true);
            } else {
                return $this->response('Holidays cannot be created.', null, false);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    public function update(UpdateRequest $request, Holidays $Holidays, $id)
    {

        try {
            $record = $Holidays::find($id)->update($request->validated());

            if ($record) {

                return $this->response('Holidays successfully updated.', $record, true);
            } else {
                return $this->response('Holidays cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function destroy(Holidays $Holidays, $id)
    {

        if (Holidays::find($id)->delete()) {

            return $this->response('Holidays successfully deleted.', null, true);
        } else {
            return $this->response('Holidays cannot delete.', null, false);
        }
    }
    public function search(Request $request, $key)
    {
        return $this->getDefaultModelSettings($request)->where('title', 'LIKE', "%$key%")->paginate($request->per_page ?? 100);
    }
    public function deleteSelected(Request $request)
    {
        $record = Holidays::whereIn('id', $request->ids)->delete();
        if ($record) {

            return $this->response('Holidays Successfully delete.', $record, true);
        } else {
            return $this->response('Holidays cannot delete.', null, false);
        }
    }
}
