<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\facades\Validator;

class AuthController extends Controller
{
   
    public function register (Request $request)
    {
                $validateUser=Validator::make(
                $request->all(),
                [
                    'name'=>'required',
                    'email'=>'required|email|unique:users,email',
                    'password'=>'required'
                ]

                );
                if($validateUser->fails())
                {
                    return response()->json([
                    'status'=>unsuccessful,
                    'message'=>'Validation Error',
                    'errors'=>$validateUser->errors()->all()
                ],401);
                }
                else{
                $user = User::create([
                'name'=> $request->name,
                'email'=> $request->email,
                'password'=>$request->password,
                ]);
                return response()->json([
                    'status'=>true,
                    'message'=>'user created sucessfully',
                    'user'=>$user,
                ],200);
                }

    }

        public function login(Request $request)
            {
                $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => unsuccessful,
                    'message' => 'Authentication failed',
                    'errors' => $validateUser->errors()
                ], 422);
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

                $authUser = Auth::user();

                $token = $authUser->createToken("API Token")->plainTextToken;

                if ($authUser->role == 'admin') {
                    return response()->json([
                        'status' => true,
                        'message' => 'Admin logged in successfully',
                        'token' => $token,
                        'token_type' => 'bearer',
                        'role' => 'admin',
                        'redirect' => '/admin/dashboard',
                        'user' => $authUser
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'User logged in successfully',
                    'token' => $token,
                    'token_type' => 'bearer',
                    'role' => 'user',
                    'redirect' => '/home',
                    'user' => $authUser
                ]);
            }

                return response()->json([
                    'status' => unsuccessful,
                    'message' => 'Email & password do not match'
                ], 401);
     }



            public function logout(Request $request)
            {
                $user = $request->user();
                $user->tokens()->delete();


                return response()->json([
                    'status'=> true,
                    'user'=>$user,
                    'message' => 'you logged out Sucessfully',
                    ],200);


            
            }
                public function user(Request $request)
                {
                    
                    return response()->json([
                        'users'=>$request->user()
                    ]);
                }

}
