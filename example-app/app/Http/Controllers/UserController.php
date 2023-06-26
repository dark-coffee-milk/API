<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function registration(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'surname'=>'required',
            'phone'=>'required|unique:users|numeric|digits:11',
            'login'=>'required',
            'password'=>'required'
        ]);
        if ($validator->fails()){
            return response([$validator->messages()], 422);
        }
        else{
            $user = User::create(
                [
                    'first_name'=>$request->first_name,
                    'surname'=>$request->surname,
                    'phone'=>$request->phone,
                    'login'=>$request->login,
                    'password'=>Hash::make($request->password)
                ]
            );
            return response(['id'=>$user->id], 201);
        }
    }

    public function auth(Request $request){
        $validator = Validator::make($request->all(), [
            'login'=>'required',
            'password'=>'required'
        ]);
        if ($validator->fails()){
            return response([$validator->messages()], 422);
        }
        if (Auth::attempt(['login'=>$request->login, 'password'=>$request->password])) {
            $token = Str::random(80);

            $request->user()->forceFill([
                'token' => hash('sha256', $token),
            ])->save();
            return response(['token'=>$token], 200);
        }
        else{
            return response(['login'=>'Incorrect login or password'], 404);
        }
    }

    public function logout(Request $request){
        $token = $request->bearerToken();
        if ($token == null){
            return response(['message'=>'You need authorization'], 403);
        }
        $user = User::where('token', hash('sha256', $token))->first();
        if($user != null){
            $user->token = null;
            $user->save();
        }
        else{
            return response(['message'=>'You need authorization'], 403);
        }
        return response(status: 200);
    }

    public function users(Request $request){
        $token = $request->bearerToken();
        if ($token == null){
            return response(['message'=>'You need authorization'], 403);
        }
        $user = User::where('token', hash('sha256', $token))->first();
        if($user != null and $user->token == hash('sha256', $token)) {
            $array = explode(' ', $request->search);
            if(count($array) === 3){
                $data = User::select('id', 'first_name', 'surname', 'phone')
                    ->where('first_name', 'like', '%'.$array[0].'%')
                    ->where('surname', 'like', '%'.$array[1].'%')
                    ->where('phone', 'like', '%'.$array[2].'%')->get();
            }else{
                $data = User::select('id', 'first_name', 'surname', 'phone')
                    ->where('first_name', 'like', '%'.$array[0].'%')
                    ->where('surname', 'like', '%'.$array[1].'%')->get();
            }

            return response($data, 200);
        }else{
            return response(['message'=>'You need authorization'], 403);
        }
    }
}
