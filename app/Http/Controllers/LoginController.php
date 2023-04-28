<?php

namespace App\Http\Controllers;

use App\Models\Sampling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    //
    public function index()
    {
        return view('user.login');   
    }

    public function verify(Request $request)
    {
        //dd($request);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        //dd(Auth::attempt($credentials));

        if(Auth::attempt($credentials))
        {   
            $request->session()->put('username',$request->email);

            $userType = Sampling::getUserType($request->email);
            
            //dd($userType[0]->userType);

            $request->session()->put('userType',$userType[0]->userType);
            $request->session()->put('name',$userType[0]->name);

            $data = $request->session()->all();
            //dd($data);
            return redirect()->intended('home');//name of the route is inside intended
        }else{
            return redirect()->route('login')->withErrors(['Invalid email and/or password']);
        }
    }

    public function logout()
    {
    	Auth::logout();
        //Destroy session
        session()->forget(['userType', 'name']);
        session()->flush();

    	return redirect()->route('login');
    }


}
