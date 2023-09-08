<?php

namespace App\Http\Controllers;

use App\Models\Sampling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    //

    public static function AllSampling()
    {
        $rawdata = Sampling::getAllParamenter();
        //$durationParams = Sampling::GetParameterByID();

        return view('parameters',[
                                    'parameters' => $rawdata
                                    //'durationParams' => $durationParams
                                 ]);
    }

    public static function GetSamplingById($id)
    {
        $data = Sampling::GetParameterByID($id);
        return response()->json($data);
    }

    public static function UpdateSamplingByID(Request $request)
    {
        //dd($request);
        $samplingCriteriaID = $request->samplingCriteriaID;
        $maxNew = $request->maxnew;
        $minNew = $request->minnew;

        $updateRange = Sampling::UpdateParameterByID($samplingCriteriaID, $maxNew, $minNew);

        if($updateRange)
        {
         $statusMsg = 'Range values have been updated successfully.';
        }else{
         $statusMsg = 'Could not update. Please contact with Admin.';
        }
 
        return redirect()->back()->with('status',$statusMsg);

    }

    public function UpdateSampling($id)
    {
        $data = DB::table('sampling_criteria')->where('Id',$id)->first();
        return view('editParameterView',['data'=>$data]);
    }

    public function UpdateSamplingInDB(Request $request, $id)
    {
        //dd($request->new_sampling_value);

        $data = array();
        $data['sampling_value_in_percent'] = $request->new_sampling_value;

        $update =DB::table('sampling_criteria')
        ->where('Id',$id)
        ->update($data);

        if($update)
        {   
            $notification=array(
                'message'=>'Value updated successfully',
                'alert-type'=>'success'
            );

            return redirect()->route('parameter-change')->with($notification);
            
        }else{
            echo "Something is wrong";
        }
    }

    public function generateParameter()
    {
        $data = Sampling::getAllParamenter();
        $wcdata = Sampling::getAllWCData();

        //$customWC = Sampling::getAllWC();

        return view('welcome',
        [
            'parameters' => $data,
            'wcdata' => $wcdata,
        ]);
    }

    public static function AllWC()
    {
        $rawdata = Sampling::getAllWC();

        //dd($rawdata);

        return view('wc-target-update',['parameters' => $rawdata['results'],
                                         'total' => $rawdata['total']
                                        ]);
    }


    public static function GetWC($wc)
    {
        $rawdata = Sampling::getAllWC($wc);
        
        return response()->json($rawdata);
    }

    public static function UpdateWC(Request $request)
    {
       //dd($request);

       $wc = $request->wc;
       $new = $request->new;
    
       $updateWC = Sampling::UpdateWC($wc, $new);
        
       //dd($updateWC);

       if($updateWC)
       {
        $statusMsg = 'Target % has been updated successfully.';
       }else{
        $statusMsg = 'Could not update. Please contact with Admin.';
       }

       return redirect()->back()->with('status',$statusMsg);
        
    }

    public function SaveAllWC(Request $request)
    {
        //dd($request);

        //Prepare WC array
        $keys = $request->keys();
        $wc = array();
        foreach($keys as $k)
        {
            //Match with request keys
            if(preg_match('/catp-\d{4}/',$k))
            {
                $wcArray = explode('-',$k);
                //$this->printR($wcArray,0);
                // if(isset($request->$k))
                // {
                $wc[$wcArray[1]] = $request->$k;
                //}
            }
        }

        // $this->printR($wc,0);
        // echo count($wc);
        $SaveAllwc = Sampling::SaveAll($wc);
        $statusMsg = 'Custom Target for all Workcodes have been updated successfully.';
        return redirect()->back()->with('status',$statusMsg);
    }

    public function GenerateSampling(Request $request)
    {   
        
        set_time_limit(-1);
        //ini_set('max_execution_time','300');
        ini_set('max_input_vars','5000');
        //dd(ini_get('max_execution_time'));

        //dd($request->all()); //->{'m-2212'}
        $auditPerAgent = $request->{'audit-per-agent'};
        $userType = session()->get('userType'); //BL

        if(empty($userType))
        {
            return redirect()->route('login')->withErrors(['Session timed out, please login again.']);
        }

        //Check if currently anyone is already processing a request in booking table
        $bookingStatus = Sampling::bookingStatusCheck('IB');
        //$bookingStatus = NULL; //Uncomment this line
        if(!empty($bookingStatus))
        {
            return redirect()->route('home')->withErrors(['Another user is already processing a request, please try again after sometime.']);
        } 

        $date_arr = explode(' - ',$request->date_range);
        $from = $date_arr[0];
        $to = $date_arr[1];
        $assignedBy = session()->get('email');

        $dataset = $this->getDataSets($from, $to);
        //dd($dataset);
        //Check if dataset is empty or not
        if(empty($dataset)){
            return redirect()->route('home')->withErrors(['No data found for mentioned dates. Please try again with different dates.']);
        }
        //dd($dataset);

        //Set booking & booking_history table
        Sampling::bookingUpdate($assignedBy, 'IB', $from, $to);

        $outcome = $this->makeOutcomeArray($request->{'info-val'},$request->{'sad-val'},$request->{'comp-val'},$request->{'bald-val'},$request->{'cfl-val'});
        //dd($outcome);

        $duration = $this->makeDurationArray($request->{'sc-val'},$request->{'mc-val'},$request->{'lc-val'},$request->{'uc-val'});
        //dd($duration);

        $segment = $this->makeSegmentArray($request->{'pb-val'},$request->{'gr-val'},$request->{'sl-val'},$request->{'bl-val'},$request->{'ot-val'});

        //Prepare WC array
        $keys = $request->keys();
        $wc = $this->makeWCArray($keys, $request);
        //dd($wc);

        //Find Agents with Audit Count
        $agents = $this->makeAgents(Sampling::getAuditCountByAgents($userType));
        //dd($agents);

        $finalArray = $this->SelectSample($dataset, $outcome, $duration, $segment, $wc, $agents, $auditPerAgent);
        //dd($finalArray);

        //Record the output size vs audit target and get the Last insert ID
        $sampleHistoryID = Sampling::insertSamplingHistory($assignedBy, $request->{'audit-target'},count($finalArray));
        //dd($sampleHistoryDataArray);

        //Trigger the Mysql event scheduler for 10 mins
        //Event is executed after 1 min and check created_at value to match with current time, if exceeds then it truncates tmp_data_ib_so_soins table


        //Check if another user already assigned a sample today or not, if yes then show message
        $checkToday = Sampling::checkSampleGenerationToday($assignedBy);
        $checkTodayMsg = null;
        if(!empty($checkToday))
        {
            $checkTodayMsg = "Another user-".$checkToday[0]->generated_by." has already generated sample and assigned today at ".$checkToday[0]->created_at;
        }

        //Take the data from temporary table
        $selectedSample = Sampling::getSelectedSample($finalArray);
        //dd($selectedSample);

        //Show the available users
        $users = Sampling::GetUserList($userType);

        return view('showGeneratedData',
                    [
                        'users' => $users,
                        'sampleHistoryID' => $sampleHistoryID,
                        'selectedSample' => $selectedSample,
                        'notification' => 'Generated data has been locked for 10 mins. Click "ASSIGN TO USERS" button to distribute to agents. If not assigned within 10 mins sampled data will be deleted automatically.',
                        'checkTodayMsg' => $checkTodayMsg
                    ]); //->with("success","");
    }


    //Multiple Layers Filters Applied
    public function SelectSample($dataset, $outcome, $duration, $segment, $wc, $agents, $auditPerAgent)
    {
        //$this->printR($dataset,0);
        //$this->printR($wc,0);
        //$this->printR($duration,0);
        //$this->printR($outcome,1);
        //$this->printR($segment,0);
        //echo '$dataset:'.sizeof($dataset);
        //$this->printR($agents,1);

        //Now check Agent audit count
        $validAgentOnlyArray = $this->GetValidAgentOnly($dataset,'', $agents, $auditPerAgent);
        //echo '<br/>$validAgentOnlyArray:'.sizeof($validAgentOnlyArray);
        //$this->printR($validAgentOnlyArray,0);
       

        $wcOnlyArray = $this->GetWCodesOnly($validAgentOnlyArray,$wc); // //$dataset
        //echo '<br/>$wcOnlyArray:'.sizeof($wcOnlyArray);
        
        //$this->printR($dataset,0);
        //$this->printR($wcOnlyArray,0);
        //$this->printArray($dataset, $wcOnlyArray);
        //dd();
    
        $durationOnlyArray = $this->GetDurationOnly($dataset, $wcOnlyArray, $duration);
        //echo '<br/>$durationOnlyArray:'.sizeof($durationOnlyArray);
        //$this->printArray($dataset, $durationOnlyArray);

        //$this->printR($durationOnlyArray,0);

        //$finalUniqueArray = array_intersect($wcOnlyArray, $durationOnlyArray);
        //$this->printR($finalUniqueArray,1);

        $outcomeOnlyArray = $this->GetOutcomeOnly($dataset,$durationOnlyArray, $outcome);
        //echo '<br/>$outcomeOnlyArray:'.sizeof($outcomeOnlyArray);
        //$this->printR($outcomeOnlyArray,0);
        

        $segmentOnlyArray = $this->GetSegmentOnly($dataset,$outcomeOnlyArray, $segment);
        //echo '<br/>$segmentOnlyArray:'.sizeof($segmentOnlyArray);
        //$this->printR($segmentOnlyArray,0);

        //dd();

        return $segmentOnlyArray;
        //$finalUniqueArray = array_intersect($wcOnlyArray, $durationOnlyArray, $outcomeOnlyArray, $segmentOnlyArray);

    }

    public function GetWCodesOnly($validAgentOnlyArray,$wc)
    {
        //$this->printR($validAgentOnlyArray,0);
        //$this->printR($wc,0);

        $targetDelta = 6;

        $final_array = array();
        foreach($validAgentOnlyArray as $data)
        {
            //$this->printR($data,0);
            // foreach($wc as $w)
            // {   
                //$this->printR($w,0);
                //dd($data['wc']);

                if(array_key_exists($data['wc'],$wc))
                {

                    if((($wc[$data['wc']]['tar']*$targetDelta)-$wc[$data['wc']]['ach']) > 0)
                    {
                        //$wc[$a['wc']]['ach'] = $wc[$a['wc']]['ach'] + 1;
                        $wc[$data['wc']]['ach']++;
                        $final_array[] = $data['tableID'];
                        
                    }
                }
            //}
        }

        //dd($final_array);
        //$this->printR($wc,0);

        return $final_array;
        //$this->printR($final_array,1);
        //echo count($final_array);
    }

    public function GetDurationOnly($dataset,$wcOnlyArray,$duration)
    {
        //$this->printR($duration,0);

        $targetDelta = 6;
        //First get the Duration Range Array
        $durationRangeArray = $this->makeDurationRangeArray(Sampling::getDurationRange());

        //$this->printR($durationRangeArray,0);

        $final_array = array();

        foreach($wcOnlyArray as $data)
        {
            if($dataset[$data]['duration']>$durationRangeArray['sc']['min'] && $dataset[$data]['duration']<=$durationRangeArray['sc']['max'])
            {
                // echo '<br/>$dataset[$data][duration]:'.$dataset[$data]['duration'];
                // echo '<br/>MIN:'.$durationRangeArray['sc']['min'];
                // echo '<br/>MAX:'.$durationRangeArray['sc']['max'];

                if((($duration['sc']['tar']*$targetDelta) - $duration['sc']['ach'])>0)
                {
                    $duration['sc']['ach']++;
                    $final_array[] = $data;
                    //$this->printR($final_array,0);
                }


            }
            
            if($dataset[$data]['duration']>$durationRangeArray['mc']['min'] && $dataset[$data]['duration']<=$durationRangeArray['mc']['max'])
            {
                // echo '<br/>$dataset[$data][duration]:'.$dataset[$data]['duration'];
                // echo '<br/>MIN:'.$durationRangeArray['mc']['min'];
                // echo '<br/>MAX:'.$durationRangeArray['mc']['max'];

                if((($duration['mc']['tar']*$targetDelta) - $duration['mc']['ach'])>0)
                {
                    $duration['mc']['ach']++;
                    $final_array[] = $data;
                }
            }

            if($dataset[$data]['duration']>$durationRangeArray['lc']['min'] && $dataset[$data]['duration']<=$durationRangeArray['lc']['max'])
            {
                // echo '<br/>$dataset[$data][duration]:'.$dataset[$data]['duration'];
                // echo '<br/>MIN:'.$durationRangeArray['lc']['min'];
                // echo '<br/>MAX:'.$durationRangeArray['lc']['max'];

                if((($duration['lc']['tar']*$targetDelta) - $duration['lc']['ach'])>0)
                {
                    $duration['lc']['ach']++;
                    $final_array[] = $data;
                }
            }

            if($dataset[$data]['duration']>$durationRangeArray['uc']['min'] && $dataset[$data]['duration']<=$durationRangeArray['uc']['max'])
            {
                // echo '<br/>$dataset[$data][duration]:'.$dataset[$data]['duration'];
                // echo '<br/>MIN:'.$durationRangeArray['uc']['min'];
                // echo '<br/>MAX:'.$durationRangeArray['uc']['max'];

                if((($duration['uc']['tar']*$targetDelta) - $duration['uc']['ach'])>0)
                {
                    $duration['uc']['ach']++;
                    $final_array[] = $data;
                }
            }
        }

        //$this->printR($duration,0);
        //exit;

        return $final_array;
        //$this->printR($final_array,1);

        // echo count($final_array);
    }

    public function GetOutcomeOnly($dataset,$durationOnlyArray, $outcome)
    {
        $targetDelta = 4;
        $final_array = array();
        foreach($durationOnlyArray as $data)
        {
            if($dataset[$data]['outcome'] == 'INFO')
            {
                if(($outcome['info']['tar']*$targetDelta-$outcome['info']['ach'])>0)
                {
                    $outcome['info']['ach']++;
                    $final_array[] = $data;
                }
            }

            if(in_array($dataset[$data]['outcome'] ,array('SERVICE','ACT','DEACT')))
            {
                if(($outcome['sad']['tar']*$targetDelta-$outcome['sad']['ach'])>0)
                {
                    $outcome['sad']['ach']++;
                    $final_array[] = $data;
                }
            }

            if($dataset[$data]['outcome'] == 'COMP')
            {
                if(($outcome['comp']['tar']*$targetDelta-$outcome['comp']['ach'])>0)
                {
                    $outcome['comp']['ach']++;
                    $final_array[] = $data;
                }
            }

            if($dataset[$data]['outcome'] == 'BALD')
            {
                if(($outcome['bald']['tar']*$targetDelta-$outcome['bald']['ach'])>0)
                {
                    $outcome['bald']['ach']++;
                    $final_array[] = $data;
                }
            }

            if($dataset[$data]['outcome'] == 'INCIDENT')
            {
                if(($outcome['cfl']['tar']*$targetDelta-$outcome['cfl']['ach'])>0)
                {
                    $outcome['cfl']['ach']++;
                    $final_array[] = $data;
                }
            }

        }

        //$this->printR($outcome,1);
        return $final_array;
        // $this->printR($final_array,1);
        // echo count($final_array);
    }

    public function GetSegmentOnly($dataset,$outcomeOnlyArray,$segment)
    {
        $final_array = array();
        foreach($outcomeOnlyArray as $data)
        {
            if(in_array($dataset[$data]['skill'],array('11','17')))
            {
                if(($segment['plat-b2b']['tar']-$segment['plat-b2b']['ach'])> 0)   
                {
                    $segment['plat-b2b']['ach']++;
                    $final_array[] = $data; 
                }
            }

            if($dataset[$data]['skill'] == '12')
            {
                if(($segment['gold-ret']['tar']-$segment['gold-ret']['ach']) > 0)
                {
                    $segment['gold-ret']['ach']++;
                    $final_array[] = $data; 
                }
            }

            if($dataset[$data]['skill'] == '13')
            {
                if(($segment['sil']['tar']-$segment['sil']['ach']) > 0)   
                {
                    $segment['sil']['ach']++;
                    $final_array[] = $data; 
                }
            }

            if($dataset[$data]['skill'] == '14')
            {
                if(($segment['bro']['tar']-$segment['bro']['ach']) > 0)   
                {
                    $segment['bro']['ach']++;
                    $final_array[] = $data; 
                }
            }

            if($dataset[$data]['skill'] == '15')
            {
                if(($segment['oth']['tar']-$segment['oth']['ach']) > 0)   
                {
                    $segment['oth']['ach']++;
                    $final_array[] = $data; 
                }
            }
        }

        //$this->printR($segment,1);
        // echo count($final_array);

        return $final_array;
    }


    public function GetValidAgentOnly($dataset, $segmentOnlyArray=array(), $agents, $auditPerAgent)
    {

        //$targetDelta = 1;
        //echo '<br/>$agents Before:'.sizeof($agents);
        //$this->printR($dataset,0);

        //echo '<br/>$auditPerAgent:'.$auditPerAgent;
        
        //echo '<br/>$agents Before:';
        //$this->printR($agents,0);

        $final_array = array();
        $takenfromSegmentArray = 0;
        $takenFromOutsideAgentArray = 0;
        foreach ($dataset as $data)
        {
            $agent = $data['agent'];
            if(array_key_exists($agent,$agents))
            {
                //echo '<br/>$alreadyAuditedCount:'.$agent.':'.
                $alreadyAuditedCount = $agents[$agent];
                //dd($alreadyAuditedCount);

                //Removed the AuditPerAgent condition
                 if($alreadyAuditedCount < ($auditPerAgent)) //*$targetDelta
                 {
                    $takenfromSegmentArray++;
                    //'<br/>Taken:'.$data;
                    $final_array[] = $data;
                    $agents[$agent]++;
                 }
            }else{
                //If not found in $agents array then it is a new agent
                $final_array[] = $data;
                $agents[$agent] = 1;
                $takenFromOutsideAgentArray++;
                //array.
            }
        }
        
        //echo '<br/>$agents After:'.sizeof($agents);
        //$this->printR($agents,0);
        //echo '<br/>$takenfromSegmentArray:'.$takenfromSegmentArray;
        // echo '<br/>$takenFromOutsideAgentArray:'.$takenFromOutsideAgentArray;
        
        //$this->printR($agents,0);
        //$this->printR($final_array,1);

        //$this->printArray($final_array, $);

        return $final_array;
    }


    public function GetValidAgentOnly_old($dataset, $segmentOnlyArray=array(), $agents, $auditPerAgent)
    {
        //echo '<br/>$agents Before:'.sizeof($agents);
        //$this->printR($dataset,0);

        //echo '<br/>$auditPerAgent:'.$auditPerAgent;
        //$this->printR($agents,0);

        $final_array = array();
        $takenfromSegmentArray = 0;
        $takenFromOutsideAgentArray = 0;
        foreach ($dataset as $data)
        {
            $agent = $data['agent'];
            if(array_key_exists($agent,$agents))
            {
                //echo '<br/>$alreadyAuditedCount:'.$agent.':'.
                $alreadyAuditedCount = $agents[$agent];
                //dd($alreadyAuditedCount);
                if($alreadyAuditedCount < ($auditPerAgent))
                {
                    $takenfromSegmentArray++;
                    //'<br/>Taken:'.$data;
                    $final_array[] = $data;
                    $agents[$agent]++;
                }
            }else{
                //If not found in $agents array then it is a new agent
                $final_array[] = $data;
                $agents[$agent] = 1;
                $takenFromOutsideAgentArray++;
                //array.
            }
        }
        
        // echo '<br/>$agents After:'.sizeof($agents);
        // $this->printR($agents,0);
        // echo '<br/>$takenfromSegmentArray:'.$takenfromSegmentArray;
        // echo '<br/>$takenFromOutsideAgentArray:'.$takenFromOutsideAgentArray;
        $this->printR($agents,0);

        return $final_array;
    }

    public function printArray($sourceArray, $arrayWithIndex)
    {
        foreach($arrayWithIndex as $index)
        {
            $this->printR($sourceArray[$index],0);
        }
    }

    public function makeAgents($objectArray)
    {   
        $agents = array();
        foreach($objectArray as $o)
        {
            $agents[$o->agentid] = $o->cnt;
        }

        return $agents;
    }

    /*
    * This is the main function where sample is selected as per logic
    * It simply iterates over arrays and if found target data, it updates its 'ach' count
    * First checks WorkCode, Duration, Outcome then Segment.
    * In this was sample is selected
    */
    //OLD algorithm with tuning
    public function SelectSample_old($dataset, $outcome, $duration, $segment, $wc, $agents)
    {
            //$this->printR($dataset,1);
            //$arr = array();
            //$audit_target = 100;
            $final_array = array();

            //echo '<br/>Data Arr:'.count($dataset);         

            //for($i=0; $i<count($arr); $i++)
            foreach($dataset as $a)
            {
                 //Base check-1
                 if(isset($wc[$a['wc']]) && (($wc[$a['wc']]['tar']==0)))
                 {
                    unset($a['tableID']);
                    continue;
                 }

                //  //Base check-2
                //  if($duration['sc']['tar']==0 || $duration['mc']['tar']==0 || $duration['lc']['tar']==0 || $duration['uc']['tar'])
                //  {
                //     unset($a['tableID']);
                //     continue;
                //  }

                 //Base check-2

                 //Base check
                //  if($a['duration']<=20)
                //  {
                //      unset($a['tableID']);
                //      continue;
                //  }

                 //Select Workcodes
                 if(isset($wc[$a['wc']]) && (($wc[$a['wc']]['tar']-$wc[$a['wc']]['ach'])>0))
                 {
                    //echo '<br/>'.$wc[$a['wc']];

                    $wc[$a['wc']]['ach'] = $wc[$a['wc']]['ach'] + 1;
                    $final_array[] = $a['tableID'];
                    // unset($a['tableID']);
                    // continue;
                 }else{
                    $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                    }

                    //Select Duration
                    if($a['duration']>30 && $a['duration']<=50)
                    {
                            if($duration['sc']['tar']-$duration['sc']['ach'] > 0)
                            {
                                $duration['sc']['ach'] = $duration['sc']['ach']+1; 
                                $final_array[] = $a['tableID'];
                                // unset($a['tableID']);
                                // continue;
                            }
                            else{
                                $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                                //printR($a,1);
                                }
                    }
                        

                   
                        if($a['duration']>=50 && $a['duration']<=180)
                        {
                            if($duration['mc']['tar']-$duration['mc']['ach']> 0)
                            {
                            $duration['mc']['ach'] = $duration['mc']['ach']+1; 
                            $final_array[] = $a['tableID'];
                            // unset($a['tableID']);
                            // continue;
                            }else{
                                $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                                }
                        }
                    
                    
                        if($a['duration']>180 && $a['duration']<=300)
                        {
                            if($duration['lc']['tar']-$duration['lc']['ach']> 0)
                            {
                                
                                    $duration['lc']['ach'] = $duration['lc']['ach']+1; 
                                    $final_array[] = $a['tableID'];
                                    // unset($a['tableID']);
                                    // continue;
                                }else{
                                    $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                                    }
                        }
                        
                        if($a['duration']> 300)
                        {
                            if($duration['uc']['tar']-$duration['uc']['ach']> 0)
                            {
                                
                                    $duration['uc']['ach'] = $duration['uc']['ach']+1; 
                                    $final_array[] = $a['tableID'];
                                    // unset($a['tableID']);
                                    // continue;
                                }else{
                                    $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                                    }
                        }
                        

                    //Select OUTCOME
                    if(($outcome['info']['tar']-$outcome['info']['ach']) > 0)
                    {
                            if($a['outcome'] == 'INFO')
                            {
                                $outcome['info']['ach'] = $outcome['info']['ach'] +1;
                                $final_array[] = $a['tableID'];
                                // unset($a['tableID']);
                                // continue;
                            }
                        }
                        else{
                           //Target fillup
                         	$a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        	//printR($a,1);
                         }

                        

                    if(($outcome['sad']['tar']-$outcome['sad']['ach']) > 0)
                    {
                            if(in_array($a['outcome'],array('SERVICE','ACT','DEACT')))
                            {
                                $outcome['sad']['ach'] = $outcome['sad']['ach']+1;
                                $final_array[] = $a['tableID'];
                                // unset($a['tableID']);
                                // continue;
                            }
                        }
                         else
                         {
                        // //Target fillup
                         $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                         }

                    //exit;

                    if(($outcome['comp']['tar']-$outcome['comp']['ach']) > 0)
                    {
                            if($a['outcome'] == 'COMP')
                            {
                                $outcome['comp']['ach'] = $outcome['comp']['ach']+1;
                                $final_array[] = $a['tableID'];
                                // unset($a['tableID']);
                                // continue;
                            }
                    }
                        else
                        {
                        $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                         //printR($a,1);
                        }

                    if(($outcome['bald']['tar']-$outcome['bald']['ach']) > 0)
                    {
                            if($a['outcome'] == 'BALD')
                            {
                                $outcome['bald']['ach'] = $outcome['bald']['ach']+1;
                                $final_array[] = $a['tableID'];
                                // unset($a['tableID']);
                                // continue;
                            }
                    }
                        else{
                        $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        }

                    if(($outcome['cfl']['tar']-$outcome['cfl']['ach']) > 0)
                    {
                            if($a['outcome'] == 'INCIDENT')//CFL
                            {
                                $outcome['cfl']['ach'] = $outcome['cfl']['ach']+1;
                                $final_array[] = $a['tableID'];
                                // unset($a['tableID']);
                                // continue;
                            }
                    }
                        else{
                        $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        }

                    
                    //Select SEGMENT
                    if($segment['plat-b2b']['tar']-$segment['plat-b2b']['ach']> 0)
                    {
                        if(in_array($a['skill'],array('11','17')))
                        {
                            $segment['plat-b2b']['ach'] = $segment['plat-b2b']['ach']+1; 
                            $final_array[] = $a['tableID'];
                            // unset($a['tableID']);
                            // continue;
                        }
                    }
                        else{
                        $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        }

                    if($segment['gold-ret']['tar']-$segment['gold-ret']['ach']> 0)
                    {
                        if($a['skill'] == '12')
                        {
                            $segment['gold-ret']['ach'] = $segment['gold-ret']['ach']+1; 
                            $final_array[] = $a['tableID'];
                            // unset($a['tableID']);
                            // continue;
                        }
                    }
                        else{
                        $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        }

                    if($segment['sil']['tar']-$segment['sil']['ach']> 0)
                    {
                        if($a['skill'] == '13')
                        {
                            $segment['sil']['ach'] = $segment['sil']['ach']+1; 
                            $final_array[] = $a['tableID'];
                            // unset($a['tableID']);
                            // continue;
                        }
                    }
                        else{
                        $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        }

                    if($segment['bro']['tar']-$segment['bro']['ach']> 0)
                    {
                        if($a['skill'] == '14')
                        {
                            $segment['bro']['ach'] = $segment['bro']['ach']+1; 
                            $final_array[] = $a['tableID'];
                            // unset($a['tableID']);
                            // continue;
                        }
                    }
                        else
                        {
                        	$a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        }

                    if($segment['oth']['tar']-$segment['oth']['ach']> 0)
                    {
                        if($a['skill'] == '15')
                        {
                            $segment['oth']['ach'] = $segment['oth']['ach']+1; 
                            $final_array[] = $a['tableID'];
                            // unset($a['tableID']);
                            // continue;
                        }
                    }
                        else
                        {
                        $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        }
                     
                    //$final_array = array_unique($final_array);

                    //if(sizeof($final_array) == $audit_target)
                    //break;
                    //$i++;
            }

            //$this->printR($dataset,0);
            // $this->printR($wc,0);
            // $this->printR($duration,0);
            // $this->printR($outcome,0);
            // $this->printR($segment,1);
            
            $final_array = array_unique($final_array);
            //echo '<br/>Unique:';
            //printR($final_array,0);

            $final_array = array_values($final_array);
            //echo '<br/>Rearrange:';
            
            //$this->printR($final_array,1);

            //echo sizeof($final_array);

            return $final_array;

            //dd($final_array);

            //die;

            // foreach ($final_array as $f)
            // {
            //     $string .= $f.",";
            // }
            //echo $string;
    }

    public function getDataSets($from, $to)
    {
        ini_set('memory_limit','1024M');

        $data = Sampling::getDataSetFromTable($from, $to);
        //dd($data);

        $arr = array();

        foreach($data as $d)
        {
            //$arr[$row['tableID']]
            $arr[$d->parentID] = array(
                        'tableID' => $d->parentID,
                        'outcome' => $d->OUTCOME,
                        'duration' => $d->DURATION,
                        'skill' => $d->SKILLNO,
                        'wc' => $d->CODE,
                        'agent' => $d->AGENTID
            );
        }

        return $arr;
    }

    public function makeOutcomeArray($info, $sad, $comp, $bald, $cfl)
    {
        return $outcome = array(
            'info' => array('tar'=>$info,'ach'=>0),
	        'sad' => array('tar'=>$sad,'ach'=>0),
	        'comp' => array('tar'=>$comp,'ach'=>0),
	        'bald' => array('tar'=>$bald,'ach'=>0),
	        'cfl' => array('tar'=>$cfl,'ach'=>0)
        );
    }

    public function makeDurationArray($sc, $mc, $lc, $uc)
    {
        return $duration = array(
            'sc' => array('tar'=>$sc,'ach'=>0),
	        'mc' => array('tar'=>$mc,'ach'=>0),
	        'lc' => array('tar'=>$lc,'ach'=>0),
	        'uc' => array('tar'=>$uc,'ach'=>0)
        );
    }

    public function makeSegmentArray($pb, $gr, $sl, $bl, $ot)
    {
        return $segment = array(
            'plat-b2b' => array('tar'=>$pb,'ach'=>0),
	        'gold-ret' => array('tar'=>$gr,'ach'=>0),
	        'sil' => array('tar'=>$sl,'ach'=>0),
	        'bro' => array('tar'=>$bl,'ach'=>0),
	        'oth' => array('tar'=>$ot,'ach'=>0)
        );
    }

    public function makeWCArray($keys, $request)
    {
        $wc = array();
        foreach($keys as $k)
        {
            //echo $k;
            //Match with request keys
            if(preg_match('/wc-\d{4}/',$k))
            {
                //if()
                $wcArray = explode('-',$k);
                $wc [$wcArray[1]] = array(
                    'tar' => $request->$k,
                    'ach' => 0
                );
            }
        }
        //dd($wc);
        return $wc;
    }

    public function makeDurationRangeArray($objectArray)
    {
        //dd($objectArray);

        $durationRangeArray = array();

        foreach($objectArray as $o)
        {
            $durationRangeArray[$o->identifier] = array(
                                                            'min' => $o->min,
                                                            'max' => $o->max
                                                        );
        }

        //dd($durationRangeArray);

        return $durationRangeArray;
    }

    public function AssignToUsers(Request $request)
    {
        //dd($request);
        set_time_limit(-1);
        
        $userType = session()->get('userType'); //BL
        if(empty($userType))
        {
            return redirect()->route('login')->withErrors(['Session timed out, please login again.']);
        }

        $fixedCalls = $request->fixedCalls;
        $selectedUsers = $request->users;
        $sampleHistoryID = $request->sampleHistoryID;

        //$userType = session()->get('userType'); //BL
        //$users = Sampling::GetUserList($user);
        $users = Sampling::GetUserListByUserID($selectedUsers);

        //dd($users);

        //$this->printR($users,0);
        //echo 'users:'.
        $totalUsers = sizeof($users);

        $callIDs = Sampling::GetCallIDs();
        //dd($callIDs);    
        //echo 'callids:'.
        $totalCalls = sizeof($callIDs);
        if($totalCalls == 0)
        {
            return redirect()->route('home')->withErrors(['Selected data has been expired, please try again.']);
        }
        //$this->printR($callIDs,0);

        //Validations
        if(($fixedCalls*$totalUsers) > $totalCalls)
        {
            return redirect()->route('home')->withErrors(['Assigned data is greater than available calls. Please check again.']);
        }else if($fixedCalls > $totalCalls)
        {
            return redirect()->route('home')->withErrors(['Fixed Calls is greater than available calls. Please check again.']);
        }

        //Condition for fixed number of calls
        if($request->fixedCalls){
            $eachUser = $fixedCalls;
            $remaining = 0;
        }else{
            $eachUser = floor($totalCalls / $totalUsers);
            $remaining = $totalCalls % $totalUsers;
        }        

        //dd($eachUser,$remaining);
        $assigned_users_arr = array();
        
        //Assign to users
        $user_with_calls = array();

        //$this->printR($callIDs,0);
        //$this->printR($users,0);

        for($i=0; $i<sizeof($users); $i++)
        {
            $random_callids = array_rand($callIDs, $eachUser);
            $user = $users[$i]->user_id;
            $user_with_calls[$user] = $random_callids;
            
            //$this->printR($random_callids,0);
            //$this->printR($user_with_calls,0);

            if($eachUser == 1)
            {
                $user_with_calls[$user] = array($random_callids);
            }
            
            //Now remove those element from callid array which is already assigned
            $j=0;
            foreach($user_with_calls[$user] as $u)
            {
                // for($j=0; $j<sizeof($u); $j++)
                // {
                    //echo '$j:'.$j;
                    //$key = $u[$j];
                    $key = $u;
                    //echo '<br/>key:'.$key;
                    //exit;
                    //print_r($callIDs[$key]);

                    $user_with_calls[$user][$j] = $callIDs[$key];

                    unset($callIDs[$key]);
                    //$this->printR($user_with_calls,0);
                    //exit;
                //}
                $j++;
            }

            //dd();
        }

        //$this->printR($user_with_calls,0);
        //$this->printR($callIDs,0);

        //Check if there are remaining items left
         if($remaining > 0)
         {
             foreach($callIDs as $cid)
             {
                 $user = array_rand($user_with_calls,1);
                 array_push($user_with_calls[$user], $cid);
                 //$user_with_calls[$user][] = $cid;
                //unset($callIDs[$cid]);
             }
         }
        
        //$this->printR($user_with_calls,1);

        //  $size = 0;
        //  foreach($user_with_calls as $u)
        //  {
        //     $size = $size + sizeof($u);
        //  }
        //  echo $size;

        //Now insert this data in assigned_ib table
        $insertedCallsWithUsers = Sampling::assignCallsToUsers($user_with_calls);

        //Now update source table with pickupFlag=1
        $results = Sampling::UpdateSourceTable($insertedCallsWithUsers);
        //dd($results);

        //Truncate booking table
        Sampling::truncateBookingTable();

        //Update Booking history table with isAssigned status=1
        $updateBookingHistoryStatus = Sampling::updateBookingHistoryStatus($sampleHistoryID);
        
        //exit;

        return redirect()->intended('home')->with("success","Total ".count($insertedCallsWithUsers)." calls have been assigned to $totalUsers agents.");
    }

    public static function GetAssignedCalls($userid)
    {
        //dd($userid);

        $call = Sampling::GetAssignedCallsFromDB($userid);

        //dd($call);

        return $call;

    }

    public static function UpdateIgnoredCalls($callid, $userid, $reason)
    {
        //dd($reason);

        $rowsAffected = Sampling::UpdateIgnoredCallsInDB($callid, $userid, $reason);

        //dd($call);

        return $rowsAffected;

    }

    public static function UpdateAuditStatus($callid, $userid)
    {
        //dd($reason);

        $rowsAffected = Sampling::UpdateAuditStatusInDB($callid, $userid);

        //dd($call);

        return $rowsAffected;

    }

    public static function checkAuditStatus($type)
    {
        $status = Sampling::checkAuditStatus($type);
    }

    public function printR($arr, $die='0')
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';

        if($die == 1)
            die;
    }

}
