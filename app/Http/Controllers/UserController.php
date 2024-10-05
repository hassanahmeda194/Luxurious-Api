<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponse;

    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['sometimes', 'image'],
            'username' => ['required'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'phone_number' => ['required'],
            'address' => ['required'],
            'country' => ['required'],
            'city' => ['required'],
            'zip_code' => ['sometimes'],
            'state' => ['required'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            if (!User::where('id', $id)->exists()) {
                return $this->error("User not found.", 404);
            }

            $user_info = auth()->user()->user_infos()->create([
                'username' => $request->username,
                'gender' => $request->gender,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'country' => $request->country,
                'city' => $request->city,
                'zip_code' => $request->zip_code,
                'state' => $request->state,
            ]);

            if ($request->filled('image')) {
                $filename = $request->file('image')->store('profile_image', 'public');
                $path = 'storage/' . $filename;
                $user_info->image = $path;
                $user_info->save();
            }

            return $this->success('profile Created Successfully', [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred during profile creation." . $th->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['sometimes', 'image'],
            'username' => ['sometimes'],
            'gender' => ['sometimes', 'in:Male,Female,Other'],
            'phone_number' => ['sometimes'],
            'address' => ['sometimes'],
            'country' => ['sometimes'],
            'city' => ['sometimes'],
            'zip_code' => ['sometimes'],
            'state' => ['sometimes'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user_info = UserInfo::where('user_id', $id)->first();
            if (!$user_info) {
                return $this->error("User not found.", 404);
            }
            $user_info->update([
                'username' => $request->username,
                'gender' => $request->gender,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'country' => $request->country,
                'city' => $request->city,
                'zip_code' => $request->zip_code,
                'state' => $request->state,
            ]);

            if ($request->filled('image')) {
                $filename = $request->file('image')->store('profile_image', 'public');
                $path = 'storage/' . $filename;
                $user_info->image = $path;
                $user_info->save();
            }

            return $this->success('profile Update Successfully', [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred during profile Update.", 500);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => ['required', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = User::find($id);
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->error("Old password doesn't match!", 500);
            }
            $user->password = Hash::make($request->new_password);
            $user->save();
            return $this->success('password updated Successfully!', [], 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred during profile update.", 500);
        }
    }
}
