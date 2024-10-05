<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Mail\SendResetPassword;
use App\Mail\SendVerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error("User Not Found", 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->error("Invalid Credentials", 401);
        }

        $token = $user->createToken($user->email)->plainTextToken;

        return $this->success("User Login Successfully!", [
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            if (User::where('email', $request->email)->exists()) {
                return $this->error("Email Already Registered", 500, [
                    "Email Already Registered"
                ]);
            };
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 3,
            ]);
            $verificationCode = rand(1000, 9999);
            Mail::to($user->email)->send(new SendVerificationCode($user, $verificationCode));
            $user->verification_code = $verificationCode;
            $user->save();
            return $this->success(message: 'Registration successful. Please check your email for the verification code.', data: [
                'user' => $user
            ], statusCode: 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred during registration.", 500, [
                'error' => $th->getMessage()
            ]);
        }
    }

    // Verify Code Function
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'code' => ['required', 'integer', 'digits:4'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->error("User not found.", 404);
            }
            if ($user->verification_code == $request->code) {
                $user->email_verified_at = now();
                $user->verification_code = null;
                $user->save();
                $token = $user->createToken($user->email)->plainTextToken;
                return $this->success(message: "Email verified successfully.", data: [
                    'token' => $token,
                    'user' => $user
                ], statusCode: 200);
            } else {
                return $this->error("Invalid verification code.", 400);
            }
        } catch (\Exception $e) {
            return $this->error("An error occurred during verification.", 500, [
                'error' => $e->getMessage()
            ]);
        }
    }


    // Resend Verification Code
    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }


        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->error("User not found.", 404);
            }

            $verificationCode = rand(1000, 9999);
            Mail::to($user->email)->send(new SendVerificationCode($user, $verificationCode));
            $user->verification_code = $verificationCode;
            $user->save();

            return $this->success('Please check your email for the verification code.', [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while resending the verification code.", 500);
        }
    }

    // Send Reset Password
    public function sendResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email']
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }


        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->error("User not found.", 404);
            }

            $otp = rand(1000, 9999);
            Mail::to($user->email)->send(new SendResetPassword($user, $otp));
            $user->reset_otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            return $this->success('A password reset OTP has been sent to your email.', [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while sending the OTP.", 500);
        }
    }

    // Verify Reset Password OTP
    public function verifyResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:4'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->error("User not found.", 404);
            }

            if ($user->reset_otp != $request->otp) {
                return $this->error("Invalid OTP code.", 400);
            }

            if ($user->otp_expires_at < now()) {
                return $this->error("OTP has expired.", 400);
            }

            return $this->success('OTP verified successfully.', [
                'verified' => true,
                'user' => $user
            ], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while verifying the OTP.", 500);
        }
    }

    // Update Password Function
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'password' => ['required', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->error("User not found.", 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return $this->success('Password updated successfully.', [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while updating the password.", 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            if (Auth::check()) {
                $request->user()->currentAccessToken()->delete();
            }

            return $this->success('User logged out successfully.', [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while logging out: " . $th->getMessage(), 500);
        }
    }
}
