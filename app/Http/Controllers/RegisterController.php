<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{

    public function create()
    {
        return view('user.register');   
    }


    public function store(Request $request)
    {   
        $input = $request->all();

        //dd($input);

        User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'userType' => $input['userType']
        ]);

        return view('user.login');
    }
}
