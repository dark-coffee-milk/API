<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CertificateController extends Controller
{
    public function new_certificate(Request $request){
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'place'=>'required',
            'photo'=>'required|mimes:jpeg,bmp,gif,svg,png|max:50000',
            'user_id'=>'required'
        ]);
        $token = $request->bearerToken();
        if ($token == null){
            return response(['message'=>'You need authorization'], 403);
        }
        $user = User::where('token', hash('sha256', $token))->first();
        if ($validator->fails()){
            return response([$validator->messages()], 422);
        }
        if($user != null and $user->id == $request->user_id){
            $certificate = Certificate::create([
                'name'=>$request->name,
                'place'=>$request->place,
                'user_id'=>$user->id,
            ]);
            $file = $request->file('photo');
            $upload_folder = 'public/images/'.$user->id.'/';
            $filename = 'Img_'.$certificate->id.'.png';
            Storage::putFileAs($upload_folder, $file, $filename);
            $certificate_up = Certificate::find($certificate->id);
            $certificate_up->photo = 'http://example-app/public/'.Storage::url($upload_folder.$filename);
            $certificate_up->save();
            return response([
                'id'=>$certificate->id,
                'url'=>'http://example-app/public'.Storage::url($upload_folder.$filename),
            ], 201);
        }else{
            return response(['message'=>'You need authorization'], 403);
        }
    }

    public function update_certificate(Request $request, $id){
        $token = $request->bearerToken();
        if ($token == null){
            return response(['message'=>'You need authorization'], 403);
        }
        $user = User::where('token', hash('sha256', $token))->first();

        $validator = Validator::make($request->all(), [
           'name'=>'required',
           'photo'=>'required|mimes:jpeg,bmp,gif,svg,png|max:50000'
        ]);
        if ($validator->fails()){
            return response([$validator->messages()], 422);
        }
        if($user != null and $user->token == hash('sha256', $token)){
            $certificate = Certificate::find($id);
            if ($certificate == null){
                return response(['Message'=>'Not found certificate'], 404);
            }
            if($certificate->user_id == $user->id){
                $certificate->name = $request->name;
                $certificate->save();
                $file = $request->file('photo');
                $upload_folder = 'public/images/'.$user->id.'/';
                $filename = 'Img_'.$certificate->id.'.png';
                Storage::putFileAs($upload_folder, $file, $filename);
                return response(status: 200);
            }else{
                return response(status: 403);
            }
        }else{
            return response(['message'=>'You need authorization'], 403);
        }
    }

    public function all_certificate(Request $request){
        $token = $request->bearerToken();
        if ($token == null){
            return response(['message'=>'You need authorization'], 403);
        }
        $user = User::where('token', hash('sha256', $token))->first();
        if($user != null and $user->token == hash('sha256', $token)) {
            return response([Certificate::all()]);
        }else{
            return response(['message'=>'You need authorization'], 403);
        }
    }

    public function one_certificate(Request $request, $id){
        $token = $request->bearerToken();
        if ($token == null){
            return response(['message'=>'You need authorization'], 403);
        }
        $user = User::where('token', hash('sha256', $token))->first();
        if($user != null and $user->token == hash('sha256', $token)) {
            $certificate = Certificate::find($id);
            if ($certificate == null){
                return response(['Message'=>'Not found certificate'], 404);
            }
            if ($certificate->user_id != $user->id){
                return response(['message'=>'You need authorization'], 403);
            }
            return response($certificate);
        }
        else{
            return response(['message'=>'You need authorization'], 403);
        }
    }

    public function del_certificate(Request $request, $id){
        $token = $request->bearerToken();
        if ($token == null){
            return response(['message'=>'You need authorization'], 403);
        }
        $user = User::where('token', hash('sha256', $token))->first();
        if($user != null and $user->token == hash('sha256', $token)) {
            $certificate = Certificate::find($id);
            if ($certificate == null){
                return response(['Message'=>'Not found certificate'], 404);
            }
            if ($certificate->user_id != $user->id){
                return response(['message'=>'You need authorization'], 403);
            }
            Storage::delete('public/images/'.$user->id.'/Img_'.$certificate->id.'.png');
            $certificate->delete();
            return response(status: 204);
        }
        else{
            return response(['message'=>'You need authorization'], 403);
        }
    }

    public function share(Request $request){
        $token = $request->bearerToken();
        if ($token == null){
            return response(['message'=>'You need authorization'], 403);
        }
        $user = User::where('token', hash('sha256', $token))->first();
        if($user != null and $user->token == hash('sha256', $token)) {
            $user_list = $request->user_id;
            $certificates = DB::table('certificate')
                ->select('user_id', DB::raw('GROUP_CONCAT(id) as existing_certificate'))
                ->groupBy('user_id')->whereIn('user_id', $user_list)
                ->get();

            foreach ($certificates as $el) {
                $el->existing_certificate = array_map(fn($n):string =>'http://example-app/api/certificate/'.$n, explode(',', $el->existing_certificate));
            }
            return response($certificates, 201);
        }else{
            return response(['message'=>'You need authorization'], 403);
        }
    }
}
