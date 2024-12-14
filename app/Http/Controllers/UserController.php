<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(User::with('posts')->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:users,name',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);
            if ($validated->fails()) {
                return response()->json([
                    'message' => $validated->errors()->first(),
                ], 422);
            }
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);
            return response()->json($user, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => __('Có lỗi phát sinh, vui lòng thử lại.'),
                'status' => 0,
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with('posts')->find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255|unique:users,name,' . $user->id,
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'password' => 'sometimes|min:6|max:32',
                'old_password' => 'required_with:password|string',
                'password_confirmation' => 'required_with:password|same:password',
            ], [
                'name.unique' => 'The name is already in use. Please choose another one.',
                'email.unique' => 'The email is already registered.',
                'password.min' => 'The password must be at least 6 characters.',
                'password.max' => 'The password must not exceed 32 characters.',
                'password.required' => 'Please enter a new password.',
                'old_password.required_with' => 'Please enter the old password.',
                'password_confirmation.required_with' => 'Please enter a confirmation password.',
                'password_confirmation.same' => 'Password confirmation does not match.',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            if ($request->has('password')) {
                if (!Hash::check($request->old_password, $user->password)) {
                    return response()->json([
                        'message' => 'The current password does not match.',
                    ], 400);
                }
                $request->merge(['password' => bcrypt($request->password)]);
            }
            $user->update($request->only(['name', 'email', 'password']));
            return response()->json([
                'message' => 'User updated successfully',
                'data' => $user,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => __('Có lỗi phát sinh, vui lòng thử lại.'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
