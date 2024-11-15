<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        return User::with("company")
            ->where("company_id", request("company_id", 0))
            ->where("user_type", "admin")
            ->paginate(request("per_page", 15));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required',
            'company_id' => 'required',
        ]);

        $user = [
            "name" => $validatedData['name'],
            "email" => $validatedData['email'],
            "password" => Hash::make($validatedData['password']),
            "role_id" => $validatedData['role_id'],
            "company_id" => $validatedData['company_id'],
            "is_master" => 1,
            "first_login" => 1,
            "user_type" => "admin",
        ];

        try {
            return User::create($user);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function show($id)
    {
        return User::with("company")->find($id);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required',
        ]);

        $admin =  [
            "name" => $validatedData['name'],
            "email" => $validatedData['email'],
            "role_id" => $validatedData['role_id'],
        ];


        if ($validatedData['password'] !== "********" && request("password_confirmation") !== "********") {
            $admin = [
                "password" => Hash::make($validatedData['password']),
            ];
        }

        try {
            return User::where("id", $id)->update($admin);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function destroy($id)
    {
        try {

            User::find($id)->delete();

            return response()->noContent();
        } catch (\Exception $e) {

            return $e->getMessage();
        }
    }
}
