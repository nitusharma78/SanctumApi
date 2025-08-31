<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;




class AuthController extends Controller
{
    public function signup(Request $request){
        $validateUser = Validator::make( //get all data from the form and validate their fields and return response
            $request->all(),
            [
                'name'=>'required|string|min:2',
                'email'=>'required|email|unique:users,email', //unique:users,email-> this methos check the user email is unique or not  
                'password'=>'required|min:4',
            ]
        );

        if($validateUser->fails()){// $validateUser->fails() = this method check the all the fields if any validation is fails it return the error messages in the form of json
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validateUser->errors()->all()
            ], 401);
        }

        //Here i am using the create method to create use credentials 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return response()->json([// it return the message and their  data
            'status' => true,
            'message' => 'User Created Successfully',
            'user' => $user, 
        ], 200);        
        
    }

    public function login(Request $request){
        $validateUser = Validator::make(//validate the fields
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
            ]
        );

        if($validateUser->fails()){ // return the laravel validation error
            return response()->json([
                'status' => false,
                'message' => 'Authentications Fails',
                'errors' =>  $validateUser->errors()->all() // return the error messages in the form of json
            ], 404);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $authUser = Auth::user();

            return response()->json([
                'status'  => true,
                'message' => 'User logged in successfully',
                'token'   => $authUser->createToken("API Token")->plainTextToken,
                'token_type' => 'bearer',
        ], 200);

        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Email & Password do not match.',
                ], 401);
        }


        
    }

    public function logout(Request $request){
        $user = $request->user();
        $user->tokens()->delete();//delete the user token

        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'You logged successfully', 
        ]);
    }
    
}