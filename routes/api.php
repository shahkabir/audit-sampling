<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuditControllerOutbound;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/posts/{userid}', function(){

    return response()->json([
        'posts' => [
            [
                'title' => 'This is a title',
                'post' => 'This is a post'
            ]
        ]

    ]);

});

//Getting CallID API
Route::get('/calls/{userid}', function($userid){

    $calls = AuditController::GetAssignedCalls($userid);
    // function(){
    //     return response()->json([
    //         'posts' => [
    //             [
    //                 'title' => 'This is a title',
    //                 'post' => 'This is a post'
    //             ]
    //         ]

    //     ]);

    // }

        return response()->json(
            $calls
        );//'call' => 
});

//Ignore API
Route::get('/callid/{callid}/userid/{userid}/reason/{reason}',function($callid, $userid, $reason){
    
    
    $rowsAffected = AuditController::UpdateIgnoredCalls($callid, $userid, $reason);

    return response()->json(
        ['rowsAffected' => $rowsAffected]
    );

});

//Update Audit Status API
//Not in use
Route::get('/callid/{callid}/userid/{userid}', function($callid, $userid){

    $rowsAffected = AuditController::UpdateAuditStatus($callid, $userid);

    return response()->json(
        ['rowsAffected' => $rowsAffected]
    );
});


Route::get('/checkAuditStatus/type/{type}',function($type){

    $status = AuditController::checkAuditStatus($type);

});


//OUTBOUND Routes
Route::get('/calls-outbound/{userid}', function($userid){

    $calls = AuditControllerOutbound::GetAssignedCalls($userid);
        return response()->json(
            $calls
        );
});

//Update Ignore status
Route::get('/soticketid/{callid}/userid/{userid}/reason/{reason}',function($callid, $userid, $reason){
    
    $rowsAffected = AuditControllerOutbound::UpdateIgnoredCalls($callid, $userid, $reason);

    return response()->json(
        ['rowsAffected' => $rowsAffected]
    );

});

//Check Audit per agent count as per set value with user
Route::get('/getAgentCountValidity/{agentid}/userid/{userid}',function($agentid, $userid){

    $valid = AuditControllerOutbound::GetAgentCountValidity($agentid, $userid);
    //dd($valid);

    return response()->json(
        ['valid' => $valid]
    );
});