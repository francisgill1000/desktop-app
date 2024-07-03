<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Member\UpdateRequest;
use App\Http\Requests\Community\Member\StoreRequest;
use App\Models\Community\Member;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function memberList($id)
    {
        return Member::where("tanent_id", $id)->get();
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

            $data = $request->validated();

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }

            Member::create($data);
            return $this->response('Member successfully created.', null, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function memberUpdate(UpdateRequest $request, $id)
    {
        try {

            $data = $request->validated();

            if (isset($request->profile_picture)) {
                $file = $request->file('profile_picture');
                $ext = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $ext;
                $request->file('profile_picture')->move(public_path('/community/profile_picture'), $fileName);
                $data['profile_picture'] = $fileName;
            }

            Member::where("id", $id)->update($data);
            return $this->response('Member successfully updated.', null, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            if (Member::find($id)->delete()) {
                return $this->response('Member successfully deleted.', null, true);
            } else {
                return $this->response('Member cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function get_member_types()
    {
        return Member::$member_types;
    }
}
