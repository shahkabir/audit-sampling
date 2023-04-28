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
        return view('parameters',['parameters' => $rawdata]);
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

        return view('welcome',
        [
            'parameters' => $data,
            'wcdata' => $wcdata
        ]);
    }


    public function GenerateSampling(Request $request)
    {
        //dd($request->all()); //->{'m-2212'}
        
        $date_arr = explode(' - ',$request->date_range);
        $from = $date_arr[0];
        $to = $date_arr[1];

        //Set booking & booking_history table
        Sampling::bookingUpdate('User', $from, $to);

        $dataset = $this->getDataSets($from, $to);
        //dd($dataset);

        $outcome = $this->makeOutcomeArray($request->{'info-val'},$request->{'sad-val'},$request->{'comp-val'},$request->{'bald-val'},$request->{'cfl-val'});
        //dd($outcome);

        $duration = $this->makeDurationArray($request->{'sc-val'},$request->{'mc-val'},$request->{'lc-val'},$request->{'uc-val'});
        //dd($duration);

        $segment = $this->makeSegmentArray($request->{'pb-val'},$request->{'gr-val'},$request->{'sl-val'},$request->{'bl-val'},$request->{'ot-val'});

        //Prepare WC array
        $keys = $request->keys();
        $wc = $this->makeWCArray($keys, $request);
        //dd($wc);

        $finalArray = $this->SelectSample($dataset, $outcome, $duration, $segment, $wc);
        //dd($finalArray);

        //Record the output size vs audit target
        $insertSampleGenerationHistory = Sampling::insertSamplingHistory($request->{'audit-target'},count($finalArray));

        
        //Take the data from table
        $selectedSample = Sampling::getSelectedSample($finalArray);
        //dd($selectedSample);

        return view('showGeneratedData',['selectedSample'=>$selectedSample]);

         
    }

    public function SelectSample($dataset, $outcome, $duration, $segment, $wc)
    {
            //$arr = array();
            $audit_target = 100;
            $final_array = array();

            //echo '<br/>Data Arr:'.count($dataset);         

            //for($i=0; $i<count($arr); $i++)
            foreach($dataset as $a)
            {

                    if(($outcome['info']['tar']-$outcome['info']['ach']) > 0)
                    {
                            if($a['outcome'] == 'INFO')
                            {
                                $outcome['info']['ach'] = $outcome['info']['ach'] +1;
                                $final_array[] = $a['tableID'];
                            }
                        }
                        // else{
                        //    //Target fillup
                        //  	$a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // 	//printR($a,1);
                        //  }

                        

                    if(($outcome['sad']['tar']-$outcome['sad']['ach']) > 0)
                    {
                            if(in_array($a['outcome'],array('SERVICE','ACT','DEACT')))
                            {
                                $outcome['sad']['ach'] = $outcome['sad']['ach']+1;
                                $final_array[] = $a['tableID'];
                            }
                        }
                        //  else
                        //  {
                        // // //Target fillup
                        //  $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        //  }

                    //exit;

                    if(($outcome['comp']['tar']-$outcome['comp']['ach']) > 0)
                    {
                            if($a['outcome'] == 'COMP')
                            {
                                $outcome['comp']['ach'] = $outcome['comp']['ach']+1;
                                $final_array[] = $a['tableID'];
                            }
                    }
                        // else
                        // {
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        //  //printR($a,1);
                        // }

                    if(($outcome['bald']['tar']-$outcome['bald']['ach']) > 0)
                    {
                            if($a['outcome'] == 'BALD')
                            {
                                $outcome['bald']['ach'] = $outcome['bald']['ach']+1;
                                $final_array[] = $a['tableID'];
                            }
                    }
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if(($outcome['cfl']['tar']-$outcome['cfl']['ach']) > 0)
                    {
                            if($a['outcome'] == 'CFL')
                            {
                                $outcome['cfl']['ach'] = $outcome['cfl']['ach']+1;
                                $final_array[] = $a['tableID'];
                            }
                    }
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if($duration['sc']['tar']-$duration['sc']['ach'] > 0)
                    {
                        if($a['duration']>=0 && $a['duration']<=60)
                        {
                            $duration['sc']['ach'] = $duration['sc']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        //  //printR($a,1);
                        // }

                    if($duration['mc']['tar']-$duration['mc']['ach']> 0)
                    {
                        if($a['duration']>=61 && $a['duration']<=150)
                        {
                            $duration['mc']['ach'] = $duration['mc']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                    
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if($duration['lc']['tar']-$duration['lc']['ach']> 0)
                    {
                        if($a['duration']>=151 && $a['duration']<=300)
                        {
                            $duration['lc']['ach'] = $duration['lc']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if($duration['uc']['tar']-$duration['uc']['ach']> 0)
                    {
                        if($a['duration']> 300)
                        {
                            $duration['uc']['ach'] = $duration['uc']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if($segment['plat-b2b']['tar']-$segment['plat-b2b']['ach']> 0)
                    {
                        if(in_array($a['skill'],array('11','17')))
                        {
                            $segment['plat-b2b']['ach'] = $segment['plat-b2b']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if($segment['gold-ret']['tar']-$segment['gold-ret']['ach']> 0)
                    {
                        if($a['skill'] == '12')
                        {
                            $segment['gold-ret']['ach'] = $segment['gold-ret']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if($segment['sil']['tar']-$segment['sil']['ach']> 0)
                    {
                        if($a['skill'] == '13')
                        {
                            $segment['sil']['ach'] = $segment['sil']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                        // else{
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if($segment['bro']['tar']-$segment['bro']['ach']> 0)
                    {
                        if($a['skill'] == '14')
                        {
                            $segment['bro']['ach'] = $segment['bro']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                        // else
                        // {
                        // 	$a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }

                    if($segment['oth']['tar']-$segment['oth']['ach']> 0)
                    {
                        if($a['skill'] == '15')
                        {
                            $segment['oth']['ach'] = $segment['oth']['ach']+1; 
                            $final_array[] = $a['tableID'];
                        }
                    }
                        // else
                        // {
                        // $a['outcome'] = $a['duration'] = $a['skill'] = $a['wc'] = '';
                        // }
                     
                     //Select Workcodes
                     if(isset($wc[$a['wc']]) && (($wc[$a['wc']]['tar']-$wc[$a['wc']]['ach'])>0))
                     {

                        $wc[$a['wc']]['ach'] = $wc[$a['wc']]['ach'] + 1;
                        $final_array[] = $a['tableID'];
                     }
                    //$final_array = array_unique($final_array);

                    //if(sizeof($final_array) == $audit_target)
                    //break;
                    //$i++;
            }

            // $this->printR($dataset,0);
            // $this->printR($outcome,0);
            // $this->printR($duration,0);
            // $this->printR($segment,0);
            // $this->printR($wc,1);
            // $this->printR($final_array,1);


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
        $data = Sampling::getDataSetFromTable($from, $to);

        $arr = array();

        foreach($data as $d)
        {
            //$arr[$row['tableID']]
            $arr[$d->parentID] = array(
                        'tableID' => $d->parentID,
                        'outcome' => $d->OUTCOME,
                        'duration' => $d->DURATION,
                        'skill' => $d->SKILLNO,
                        'wc' => $d->CODE
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

    public function AssignToUsers(Request $request)
    {
        $user = session()->get('userType'); //BL
        $users = Sampling::GetUserList($user);
        //dd($users);
        $this->printR($users,0);
        echo 'users:'.$totalUsers = sizeof($users);

        $callIDs = Sampling::GetCallIDs();
        //dd($callIDs);    
        echo 'callids:'.$totalCalls = sizeof($callIDs);
        $this->printR($callIDs,0);

        echo 'eachuser:'.$eachUser = floor($totalCalls / $totalUsers);

        echo 'remaining:'.$remaining = $totalCalls % $totalUsers;

        $assigned_users_arr = array();
        
        //Assign to users
        $user_with_calls = array();

        for($i=0; $i<sizeof($users); $i++)
        {
            $random_callids = array_rand($callIDs,$eachUser);
            $user = $users[$i]->user_id;
            $user_with_calls[$user] = $random_callids;
            //$this->printR($user_with_calls,0);
            //Now remove those element from callid array which is already assogned
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
        }

        $this->printR($user_with_calls,0);
        $this->printR($callIDs,0);

        //Check if there are remaining items left
         if($remaining > 0)
         {
             foreach($callIDs as $cid)
        //     {
                 $user = array_rand($assigned_users_arr,1);
                 $assigned_users_arr[$user][] = $cid;
                //unset($callIDs[$cid]);
             }
         }

        //$this->printR($callIDs,0);

        //$this->printR($users,0);

        $this->printR($assigned_users_arr,1);

        //dd($eachUser);
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

    public function printR($arr, $die='0')
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';

        if($die == 1)
            die;
    }

}
