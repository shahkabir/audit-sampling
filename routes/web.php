<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Models\Listing;
use App\Models\Listings;
use App\Models\Sampling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as RoutingRoute;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//HTTP request
Route::get('/', //function () {
    //return view('login');
    //[AuditController::class,'generateParameter']
    [LoginController::class,'index']
//}
)->name('login');

Route::post('/verify', [LoginController::class,'verify'])->name('verify');
Route::get('/logout', [LoginController::class,'logout'])->name('logout');


Route::get('/register', [RegisterController::class,'create'])->name('register');
Route::post('/newUser', [RegisterController::class,'store'])->name('newUser');


Route::get('/home',[AuditController::class,'generateParameter'])->name('home');

Route::get('/parameter-change', [AuditController::class,'AllSampling'])->name('parameter-change');

Route::get('/update-sampling/{id}', [AuditController::class,'UpdateSampling']);

Route::post('/process-sampling/{id}',[AuditController::class,'UpdateSamplingInDB']);

// Route::get('/parameter-change', function () {
    
//     $rawdata = Sampling::getAllParamenter();
    
//     return view('parameters',['parameters' => $rawdata]);
// });

Route::post('/generate-sampling',[AuditController::class,'GenerateSampling']);

Route::post('/assign-to-users',[AuditController::class,'AssignToUsers']);

Route::get('/showGeneratedData', function(){
    return view('showGeneratedData');
});


Route::get('/hello', function () {
    //return 'Hello World';

    return response('<h1>Hello World</h1>');
});

Route::get('/posts/{id}/value/{v}',function($id, $v){

    //debugging
    dd($id);
    ddd($id);

    return response('Post:'.$id.' Value:'.$v);

})->where('id','[0-9]+'); //ensures only numbers are provided in id else 404 thrown

//Take variables from URL
Route::get('/search',function(Request $request){
    //dd($request);
    return $request->city.' '.$request->country;
});

//All Listing
Route::get('/bladetesting', function(){
    return view('listings',[
        'heading' => 'Latest Listings',
        'listings' => Listing::all()
    ]
    );
});

//Single List
Route::get('/single/{id}',function($id){
    //dd($id);
    return view('listing',[
        'list' => Listings::find($id)
    ]);
});

//Get parameters


