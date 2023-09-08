<?php

use App\Models\Listing;
use App\Models\Listings;
use App\Models\Sampling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RegisterController;
use Illuminate\Routing\Route as RoutingRoute;
use Spatie\FlareClient\Solutions\ReportSolution;
use App\Http\Controllers\AuditControllerOutbound;

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
Route::get('/parameter-get/{id}', [AuditController::class,'GetSamplingById'])->name('parameter-get');
Route::put('/update-parameters',[AuditController::class,'UpdateSamplingByID'])->name('update-params');

//WC update
Route::get('/update-wc',[AuditController::class,'AllWC'])->name('wc-change');
Route::get('/get-wc/{wc}',[AuditController::class,'GetWC'])->name('wc-get');
Route::put('/update-wc',[AuditController::class,'UpdateWC'])->name('wc-update');
Route::put('/save-wc',[AuditController::class,'SaveAllWC'])->name('wc-save-all');

Route::get('/update-sampling/{id}', [AuditController::class,'UpdateSampling']);

Route::post('/process-sampling/{id}',[AuditController::class,'UpdateSamplingInDB']);

// Route::get('/parameter-change', function () {
    
//     $rawdata = Sampling::getAllParamenter();
    
//     return view('parameters',['parameters' => $rawdata]);
// });

Route::post('/generate-sampling',[AuditController::class,'GenerateSampling'])->name('generateSampling');

Route::post('/assign-to-users',[AuditController::class,'AssignToUsers'])->name('assign-to-users');

// Route::get('/assign-to-users',function(){
//     return view('showGeneratedData');
// })->name('assign-to-users-post');


Route::get('/showGeneratedData', function(){
    return view('showGeneratedData');
});

//Outbound Routes
Route::get('/parameter-change-outbound', [AuditControllerOutbound::class,'AllSampling'])->name('parameter-change');
Route::get('/audit-per-agent-get/{tableID}',[AuditControllerOutbound::class,'GetAuditPerAgent'])->name('audit-per-agent-get');
Route::get('/parameter-change-outbound-agent', [AuditControllerOutbound::class,'AuditPerAgent'])->name('audit-per-agent');
Route::put('/update-audit-per-agent',[AuditControllerOutbound::class,'UpdateAuditPerAgentByID'])->name('update-audit-per-agent');


Route::get('/generate-outbound',[AuditControllerOutbound::class,'generateParameter'])->name('generate-outbound');
Route::post('/generate-sampling-outbound',[AuditControllerOutbound::class,'GenerateSampling'])->name('generateSamplingOutbound');
Route::post('/assign-to-users-outbound',[AuditControllerOutbound::class,'AssignToUsers'])->name('assign-to-users-outbound');


//Report Routes
Route::get('/report-assigned-calls', function(){
    return view('reports.showAssignedCalls');
});
Route::post('/show-report-assigned-calls',[ReportController::class,'AssignedCalls'])->name('show-report-assigned-calls');

Route::get('/report-assigned-calls-details', function(){
    return view('reports.showAssignedCallsDetails');
});

Route::post('/show-report-assigned-calls-details',[ReportController::class,'AssignedCallsDetails'])->name('show-report-assigned-calls-details');

Route::get('/target-vs-generated', function(){
    return view('reports.target-vs-generated');
});
Route::post('/show-target-vs-generated',[ReportController::class,'SamplingGenerationHistory'])->name('show-target-vs-generated');


Route::get('/source-data', function(){
    return view('reports.sourceDataSummary');
});
Route::post('/show-source-data',[ReportController::class,'SourceDataSummary'])->name('show-source-data');


Route::get('/phpinfo', function () {
    return phpinfo();
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


