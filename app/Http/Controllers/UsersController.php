<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index() {
        $users = User::all();

        return response()->json([
            "data" => UserResource::collection($users),
            "totalUsers" => User::count(),
            "totalAdmins" => User::where("user_type", "admin")->count(),
            "totalReaders" => User::where("user_type", "readers")->count(),
        ]);
    }

    private function checkIfUsernameExist(string $username) {
        $users = User::where("username", $username)->get();
        return count($users) > 0;
    }

    public function register(StoreUserRequest $request) {
        if ($this->checkIfUsernameExist($request->username)) {
            return response()->json([
                "error" => "Credentials taken by another user",
                "message" => "Username already exist"
            ], 403);
        }

        try {
            $admin = User::create([
                "name" => $request->name,
                "username" => $request->username,
                "password" => $request->confirmPassword,
                "user_type" => $request->admin === "true" ? "admin" : "reader"
            ]);
            $admin->status = "active";

            $token = $admin->createToken("auth-token")->plainTextToken;

            return response()->json([
                "data" => new UserResource($admin),
                "token" => $token,
                "message" => "Account created successfully"
            ]);
        }
        catch(\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Something wrong happened !!!"
            ], 500);
        }
    }

    public function login(LoginRequest $request) {
        try {
            $user = User::where("username", $request->username)->firstorFail();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    "error" => "Problem with provided credentials",
                    "message" => "Invalid password, please check and try again"
                ], 403);
            }

            $user->tokens()->delete();
            $newToken = $user->createToken("auth-token")->plainTextToken;

            return response()->json([
                "duration" => 3600,
                "data" => new UserResource($user),
                "token" => $newToken,
                "message" => "Access granted, you are logged-in successfully"
            ]);
        }
        catch(\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Invalid username and or password"
            ], 403);
        }
    }

    public function checkUsername(Request $request) {
        $username = $request->value;

        $users = User::where("username", $username)->get();

        if (count($users) > 0) {
            return response()->json([
                "message" => "taken"
            ]);
        }

        return response()->json([
            "message" => "not-yet"
        ]);
    }
}
