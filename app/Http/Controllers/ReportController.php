<?php

namespace App\Http\Controllers;

use App\Models\ReportModel;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    
    public function AssignedCalls(Request $request)
    {
        //dd($request);
        $date_arr = explode(' - ',$request->date_range);
        $from = $date_arr[0];
        $to = $date_arr[1];
        $lob = $request->lob;

        $data = ReportModel::GetAssignedCalls($from,$to,$lob);

        //dd($data);

        return view('reports.showAssignedCalls',compact('data','from','to'));
    }

    public function AssignedCallsDetails(Request $request)
    {
        //dd($request);
        $date_arr = explode(' - ',$request->date_range);
        $from = $date_arr[0];
        $to = $date_arr[1];
        $lob = $request->lob;

        $data = ReportModel::GetAssignedCallsDetails($from, $to, $lob);
        //dd($data);
        
        $headers = array();
        if(!empty($data)){
            $headers = array_keys(get_object_vars($data[0]));
        }

        return view('reports.showAssignedCallsDetails',compact('data','headers','from','to','lob'));
    }

    public function SamplingGenerationHistory(Request $request)
    {
        $date_arr = explode(' - ',$request->date_range);
        $from = $date_arr[0];
        $to = $date_arr[1];
        $lob = $request->lob;

        $data = ReportModel::GetTargetvsGenerated($from,$to,$lob);

        //dd($data);

        return view('reports.target-vs-generated',compact('data','from','to'));
    }

    public function SourceDataSummary(Request $request)
    {
        $date_arr = explode(' - ',$request->date_range);
        $from = $date_arr[0];
        $to = $date_arr[1];

        $data = ReportModel::GetSourceDataSummary($from,$to);

        //dd($data);

        return view('reports.sourceDataSummary',compact('data','from','to'));
    }
    

}
