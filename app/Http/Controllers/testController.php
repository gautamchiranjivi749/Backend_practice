<?php

namespace App\Http\Controllers;

 class testController extends Controller
{
    public function test()
        {
           try{
            $data = User::firstorFail();

             return response()->json([
                'message'=>'hello to server',
                'success'=>true,
                'status'=>200,
                'data'=>$data
            ]);
           }
           catch(Exception $e){
            return response()->json([
                'message'=>'Something went wrong',
                'success'=>false,
                'status'=>500
            
            ]);
           }
           
        }
    
}
