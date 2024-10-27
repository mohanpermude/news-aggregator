<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    // Send password reset link to the user's email
    /**
     * @OA\Post(
     *     path="/api/password/email",
     *     tags={"Authentication"},
     *     summary="Send Password Reset Link",
     *     description="Sends a password reset link to the user's email.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Password reset link sent.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid email",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $validator = Validator::make($request->all(), ['email' => 'required|email']);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['status' => __($status)], 200)
            : response()->json(['email' => __($status)], 400);
    }

    // Reset the user's password
    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     tags={"Authentication"},
     *     summary="Reset User Password",
     *     description="Resets the user's password.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "password_confirmation", "token"},
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword"),
     *             @OA\Property(property="token", type="string", example="token_string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Password reset successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or email",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->password = Hash::make($request->password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['status' => __($status)], 200)
            : response()->json(['email' => __($status)], 400);
    }
}
