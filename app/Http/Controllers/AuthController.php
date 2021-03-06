<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Models\Department;
use Socialite;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);
        $user = new User([
            'name' => $request->name,
            'gender' => $request->gender,
            'address' => $request->address,
            'phone' => $request->phone,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'parrent_name' => $request->parrent_name,
            'parrent_phone' => $request->parrent_phone,
            'email' => $request->email,
            'membership' => 0,
            'password' => bcrypt($request->password)
        ]);
        $user->save();
        $user->attachRole(2);
        return response()->json([
            'status' => true,
            'message' => 'Successfully created user!'
        ], 201);
    }
  
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        $update_device_id = User::where('id',$request->user()->id)->first();
        $update_device_id->device_id = $request->device_id;
        $update_device_id->save();
        return response()->json([
            'status' => true,
            'data' => $request->user(),
            'role' => $request->user()->roles()->first()->id,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $user = User::where('id',$request->user()->id)->first();
        $user->device_id = null;
        $user->status = 0;
        $user->save();
        $request->user()->token()->revoke();  
        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out'
        ]);
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {   
        $data = User::join('classes','classes.id','=','users.class_id')
            ->select('classes.id as class_id','classes.name_class as name_class')
            ->where('users.id','=',$request->user()->id)
            ->first();
        $option1 = Department::join('faculties','faculties.id','=','departments.faculty_id')
            ->select('faculties.id','faculties.univ_id')
            ->join('universities','universities.id','=','faculties.univ_id')
            ->select('universities.name as university_name','departments.name as department_name','departments.id as department_id' ,'universities.id as univ_id')
            ->where('departments.id',$request->user()->option1)->first();       
        $option2 = Department::join('faculties','faculties.id','=','departments.faculty_id')
            ->select('faculties.id','faculties.univ_id')
            ->join('universities','universities.id','=','faculties.univ_id')
            ->select('universities.name as university_name','departments.name as department_name','departments.id as department_id' ,'universities.id as univ_id')
            ->where('departments.id',$request->user()->option2)->first();       
        $option3 = Department::join('faculties','faculties.id','=','departments.faculty_id')
            ->select('faculties.id','faculties.univ_id')
            ->join('universities','universities.id','=','faculties.univ_id')
            ->select('universities.name as university_name','departments.name as department_name','departments.id as department_id' ,'universities.id as univ_id')
            ->where('departments.id',$request->user()->option3)->first();
        if($request->user()->photo_url == null){
            $photo = null;
        }else{
            $photo = url('/images/student/'.$request->user()->photo_url);               
        }
        return response()->json([
            'status' => true,
            'data' =>[
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'gender' => $request->user()->gender,
                'address' => $request->user()->address,
                'phone' => $request->user()->phone,
                'birth_place' => $request->user()->birth_place,
                'birth_date' => $request->user()->birth_date,
                'parrent_name' => $request->user()->parrent_name,
                'parrent_phone' => $request->user()->parrent_phone,
                'balance' => $request->user()->balance,
                'status' => $request->user()->status,
                'membership' => $request->user()->membership,
                'email' => $request->user()->email,
                'photo_url' => $photo,
                'class' => $data,
                'option1' => $option1,
                'option2' => $option2,
                'option3' => $option3
            ]  
        ],201);
        // return UserResource::collection($data);
    }

    public function changePhotoProfile(Request $request, $id){
        $request->validate([
            'photo' => 'image|required|mimes:jpeg,png,jpg,gif,svg'
          ]);
    	$data = User::where('id',$id)->first();
        $image = $request->file('photo');
        if(empty($image)){
           $namaFile = "null";
        }else{
            $extension = $image->getClientOriginalExtension();
            $namaFile = $id.'.'.$extension;
            $request->file('photo')->move('images/student', $namaFile);
        }
        $data->photo_url = $namaFile;
        $data->save();
        return response()->json([
            'status' => true,
            'message' => 'Successfully changed photo user!'
        ], 201);
    }

    public function changeProfile(Request $request, $id){
    	$data =  User::where('id',$id)->first();
        $data->name = $request->name;
        $data->gender = $request->gender;
        $data->address = $request->address;
        $data->phone = $request->phone;
        $data->birth_place = $request->birth_place;
        $data->birth_date = $request->birth_date;
        $data->parrent_name = $request->parrent_name;
        $data->parrent_phone = $request->parrent_phone;
        $data->class_id = $request->class_id;
        $data->option1 = $request->option1;
        $data->option2 = $request->option2;
        $data->option3 = $request->option3;
        $data->save();

        return response()->json([
            'status' => true,
            'message' => 'Successfully changed user!'
        ], 201);
    }

    public function changePassword(Request $request, $id){
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'newPassword' => 'required|string'
        ]);
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)){
            return response()->json([
                'status' => false,
                'message' => 'Current password wrong!'
            ], 401);
        }else{
            $data =  User::where('id',$id)->first();
            $data->password = bcrypt($request->newPassword);
            $data->save();    
            return response()->json([
                'status' => true,
                'message' => 'Successfully changed password user!'
            ], 201);
        }
    }

    public function getPhotoProfile($id){
        $data =  User::where('id',$id)->first();
        if($data->photo_url!=null){
            $pathToFile = public_path().'/images/student/'.$data->photo_url;
            return response()->download($pathToFile);        
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Photo does not exist!'
            ], 201);
        }
    }

    public function redirectToProvider($service)
    {
        return Socialite::driver($service)->redirect();
    }
    
    public function handleProviderCallback($service, Request $req)
    {
        $user = Socialite::driver($service)->stateless()->user();
        $findUser = User::where('email', $user->getEmail())->first();

        if($findUser){
            $tokenResult = $findUser->createToken($user->token);
            $findUser->update([
                'device_id' => $req->device_id
            ]);
            return response()->json([
                'status' => true,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $user->expiresIn
                )->toDateTimeString()
            ], 201);
        }
        else{
            $user_local = new User([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'device_id' => $req->device_id
            ]);
            $user_local->save();
            $user_local->attachRole(2);

            $tokenResult = $user_local->createToken($user->token);
            return response()->json([
                'status' => true,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $user->expiresIn
                )->toDateTimeString()
            ], 201);
        }
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderToken($service, Request $req)
    {
        $user = Socialite::driver($service)->stateless()->userFromToken($req->token);
        $findUser = User::where('email', $user->getEmail())->first();

        if($findUser){
            $tokenResult = $findUser->createToken($user->token);
            $findUser->update([
                'device_id' => $req->device_id
            ]);
            return response()->json([
                'status' => true,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $user->expiresIn
                )->toDateTimeString()
            ], 201);
        }
        else{
            $user_local = new User([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'device_id' => $req->device_id
            ]);
            $user_local->save();
            $user_local->attachRole(2);

            $tokenResult = $user_local->createToken($user->token);
            return response()->json([
                'status' => true,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $user->expiresIn
                )->toDateTimeString()
            ], 201);
        }
    }
}
