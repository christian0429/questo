<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getAllUsers()
    {
        return response()->json(User::all(), 200);
    }

    public function personalizeUser(Request $request)
    {
        $merged = array_merge(['user_id' => $request->user()->id, 'tags' => $request->tags]);
        $validator = Validator::make(array_merge($merged), [
            'tags' => 'required',
            'user_id' => 'required|integer|unique:user_personals,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }

        UserPersonal::create([
            'user_id' => $request->user()->id,
            'tags' => json_encode($request->tags),
        ]);

        $userPersonal = $request->user()->personal;

        $userPersonal->tags = json_decode($userPersonal->tags);

        return response()->json(['tags' => $userPersonal->tags, 'message' => 'User personalization created'], 201);
    }


    public function getUserPersonal(Request $request)
    {
        $validator = Validator::make(['user_id' => $request->user()->id], [
            'user_id' => 'required|integer|exists:user_personals,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }

        $personal = $request->user()->personal;

        $personal->tags = json_decode($personal->tags);
        return response()->json($personal, 200);
    }

    public function updateUserPersonal(Request $request)
    {
        $mergedReqs = array_merge(['tags' => $request->tags,'user_id' =>  $request->user()->id]);

        $validator = Validator::make($mergedReqs, [
            'tags' => 'required',
            'user_id' => 'required|integer|exists:user_personals,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }

        $userPersonal = $request->user()->personal;
        $userPersonal->update([
            'tags' => json_encode($request->tags),
        ]);

        $userPersonal->tags = json_decode($userPersonal->tags);

        return response()->json(['tags' => $userPersonal->tags, 'message' => 'User personalization updated'], 200);
    }

    public function deleteUser(Request $request)
    {
        $user = User::find($request->id);
        if ($user === null) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted'], 200);
    }
}
