<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $number = "";

        for($i = 0; $i < strlen($token); $i++) {
            if (strcmp($token[$i], "|") === 0) {
                break;
            }
            $number .= $token[$i];
        }

        $personAccessToken = DB::table("personal_access_tokens")->find($number);

        if ($personAccessToken === null) {
            return response()->json([
                "error" => "Invalid token",
                "message" => "Please ensure you are logged in with the right credentials",
                "data" => $number
            ]);
        }

        $user = User::find($personAccessToken->tokenable_id);

        if ($user->user_type !== "admin") {
            return response()->json([
                "error" => "Invalid user role",
                "message" => "Please ensure you are an admin user"
            ]);
        }

        return $next($request);
    }
}
