<?php

namespace App\Http\Controllers;

use App\Models\SamplingModelOutbound;
use Illuminate\Http\Request;

class AuditControllerOutbound extends Controller
{
    public static function AllSampling()
    {
        $rawdata = SamplingModelOutbound::getAllParamenter();
        return view('parameters',['parameters' => $rawdata]);
    }

    public static function AuditPerAgent($tableID = null)
    {
        $rawdata = SamplingModelOutbound::getAuditPerAgent($tableID);

        //dd($rawdata);

        return view('outbound.auditperagent',['parameters' => $rawdata]);
    }

    public function generateParameter()
    {
        $data = SamplingModelOutbound::getAllParamenter();
        $AuditPerAgent = SamplingModelOutbound::getAuditPerAgent();
        //$data=0;
        //$wcdata = 0;

        return view('outbound.generateSample',
        [
            'parameters' => $data,
            'auditPerAgent' => $AuditPerAgent
        ]);
    }

    public function GenerateSampling(Request $request)
    {
        //dd($request->all());


        $auditPerAgent = $request->{'audit-per-agent'};
        $userType = session()->get('userType'); //BL

        if(empty($userType))
        {
            //return redirect()->route('generate-outbound')->withErrors(['Session timed out, please login again.']);
            return redirect()->route('login')->withErrors(['Session timed out, please login again.']);
        }

         //Check if currently anyone is already processing a request in booking table
         $bookingStatus = SamplingModelOutbound::bookingStatusCheck('OB');
         //$bookingStatus = NULL; //Uncomment this line
         if(!empty($bookingStatus))
         {
            return redirect()->route('generate-outbound')->withErrors(['Another user is already processing a request, please try again after sometime.']);
         } 

         //dd('i am here');

        $date_arr = explode(' - ',$request->date_range);
        $from = $date_arr[0];
        $to = $date_arr[1];
        $assignedBy = session()->get('email');
        $flagArray = $this->getFlagArray($request);
        //$this->printR($flagArray,0);


        $dataset = $this->getDataSets($from, $to);
        //dd($dataset);

        //Check if dataset is empty or not
        if(empty($dataset)){
            return redirect()->route('generate-outbound')->withErrors(['No data found for mentioned dates. Please try again with different dates.']);
        }

        //Set booking & booking_history table //uncomment below line
        SamplingModelOutbound::bookingUpdate($assignedBy, 'OB', $from, $to);

        //Make the criteria arrays-
        $ticket_category = $this->makeTicketCategory($request);

        $ticket_status = $this->makeTicketStatus($request);

        $channel = $this->makeChannel($request);

        // $this->printR($ticket_category,0);

        // $this->printR($ticket_status,0);

        // $this->printR($channel,0);

        //Find Agents with Audit Count
        //Not feasible as we do not have any reference for agentID in SuperOffice tickets
        //$agents = $this->makeAgents(SamplingModelOutbound::getAuditCountByAgents($userType));

        $finalArray = $this->SelectSample($dataset, $ticket_category, $ticket_status, $channel, $flagArray);
        //dd($finalArray);

        //Record the output size vs audit target and get the Last insert ID
        $sampleHistoryID = SamplingModelOutbound::insertSamplingHistory($assignedBy, $request->{'no-of-agent'},count($finalArray));

        //Take the data from temporary table
        $selectedSample = SamplingModelOutbound::getSelectedSample($finalArray);

        //$this->printR($selectedSample,1);

        //Show the available users
        $users = SamplingModelOutbound::GetUserList($userType);

        return view('outbound.showGeneratedDataOutbound',
                    [
                        'users' => $users,
                        //'sampleHistoryID' => $sampleHistoryID,
                        'selectedSample' => $selectedSample,
                        'notification' => 'Generated data has been locked for 10 mins. Click "ASSIGN TO USERS" button to distribute to agents. If not assigned within 10 mins sampled data will be deleted automatically.',
                        //'checkTodayMsg' => $checkTodayMsg
                    ]); //->with("success","");
    }

    public function SelectSample($dataset, $ticket_category, $ticket_status, $channel, $flagArray)
    {
            // [category_flag] => 1
            // [status_flag] => 1
            // [channel_flag] => 1
            //echo '<br/>Dataset:'.sizeof($dataset);
            //$this->printR($dataset,0);

            //$this->printR($ticket_category,0);

            //$this->printR($ticket_status,0);

            //$this->printR($channel,0);
            //dd($flagArray);


            $category_flag = ($flagArray['category_flag'] == 1) ? 1 : 0;
            $status_flag = ($flagArray['status_flag'] == 1) ? 2 : 0;
            $channel_flag = ($flagArray['channel_flag'] == 1) ? 4 : 0;

            //Getting BIT wise operator
            $combination = $category_flag | $status_flag | $channel_flag;

            switch($combination){
                case 0: //We have validated this in UI
                    //echo "no choices selected";
                    
                    return redirect()->route('generate-outbound')->withErrors(['No criteria selected. Please select atleast one criteria.']);
                    break;
                case 1:
                    //echo "category_flag selected.";

                    $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category,1);
                    
                    return $CategoryOnlyArray;

                    break;
                case 2:
                    //echo "status_flag selected.";
                    $StatusOnlyArray = $this->getStatusOnly($dataset, $ticket_status, 1);

                    return $StatusOnlyArray;

                    break;
                case 3:
                    //echo "category_flag | status_flag selected.";

                    //First get categories with delta
                    $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, 10); //2
                    //echo '<br/>CategoryOnlyArray:'.sizeof($CategoryOnlyArray);
                    $CategoryOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryOnlyArray, $dataset);


                    $CategoryAndStatusArray = $this->getStatusOnly($CategoryOnlyArrayWithDataset, $ticket_status, 1);
                    //echo '<br/>CategoryAndStatusArray:'.sizeof($CategoryAndStatusArray);

                    //$this->printR($CategoryAndStatusArray,1);

                    return $CategoryAndStatusArray;

                    break;

                case 4:
                    //echo "channel_flag selected.";
                    $ChannelOnlyArray = $this->getChannelOnly($dataset, $channel, 1);
                    return $ChannelOnlyArray;
                    break;

                case 5:
                    //echo "category_flag and channel_flag selected.";

                    //First get categories with delta
                    $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, 2);

                    $CategoryOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryOnlyArray, $dataset);

                    $CategoryAndChannelArray = $this->getChannelOnly($CategoryOnlyArrayWithDataset, $channel, 1);

                    //echo '<br/>CategoryAndChannelArray:'.sizeof($CategoryAndChannelArray);

                    return $CategoryAndChannelArray;
                    break;

                case 6:
                    //echo "status_flag | channel_flag selected.";

                    //First get the status with delta
                    $StatusOnlyArray = $this->getStatusOnly($dataset, $ticket_status, 2);

                    $StatusOnlyArrayWithDataset = $this->getSelectedDataWithIndex($StatusOnlyArray, $dataset);

                    $StatusAndChannelArray = $this->getChannelOnly($StatusOnlyArrayWithDataset,$channel,1);
                    
                    return $StatusAndChannelArray;

                    break;

                case 7:
                    //echo "category_flag | status_flag | channel_flag selected.";

                    //Implementing multiple filter layer
                    
                    $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, 4); //4
                    $CategoryOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryOnlyArray, $dataset);

                    //Status filtered from category
                    $CategoryAndStatusArray = $this->getStatusOnly($CategoryOnlyArrayWithDataset, $ticket_status, 4); //4
                    $StatusOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryAndStatusArray, $dataset);

                    //Channel filtered from Status,Category
                    $CategoryAndStatusAndChannelArray = $this->getChannelOnly($StatusOnlyArrayWithDataset, $channel, 1); //2

                    return $CategoryAndStatusAndChannelArray;
                    

                    /* Reverse process
                    $channelOnlyArray = $this->getChannelOnly($dataset, $channel, 10);
                    $channelOnlyArrayWithDataset = $this->getSelectedDataWithIndex($channelOnlyArray, $dataset);


                    //Status filtered from Channel
                    $ChannelAndStatusArray = $this->getStatusOnly($channelOnlyArrayWithDataset, $ticket_status, 5);
                    $StatusOnlyArrayWithDataset = $this->getSelectedDataWithIndex($ChannelAndStatusArray, $dataset);


                    //Category filtered from Status
                    $ChannelAndStatusAndCategory = $this->getCategoryOnly($StatusOnlyArrayWithDataset, $ticket_category, 2);

                    return $ChannelAndStatusAndCategory;
                    */
                    
                    break;
            }

        
        // if($flagArray['category_flag'] == 1)
        // {
        //     $this->GetCategoryOnly();
        // }
    }

    public function getDataSets($from, $to)
    {
        ini_set('memory_limit','1024M');

        $data = SamplingModelOutbound::getDataSetFromTable($from, $to);
        
        //dd($data);

        $arr = array();

        foreach($data as $d)
        {
            //$arr[$row['tableID']]
            $arr[$d->parentID] = array(
                        'tableID' => $d->parentID,
                        'soticketID' => $d->SOTicket,
                        'msisdn' => $d->MSISDN,
                        'STATUS' => $d->STATUS,
                        'SOURCE' => $d->SOURCE,
                        'CATEGORY' => $d->CATEGORY
            );
        }

        return $arr;
    }

    public function getCategoryOnly($dataset, $ticket_category, $targetDelta = 1)
    {
        //echo $targetDelta;

        $keyMappings = array(
            'nqc_val' =>'Network Quality Complaint',
            'oth_val' =>'Others',
            'src_val' =>'Service Related Complaint',
            'prc_val' =>'Promotional Offer/Bonus Related Complaint',
            'irc_val' =>'Internet Related Complaint',
            'pcc_val' =>'Package & Charging Related Complaint',
            'vasc_val' =>'VAS Complaint',
            'sr_val' =>'Service Request',
            'rcc_val' =>'Recharge/Scratch Card Related Complaint',
            'pbc_val' =>'Postpaid Billing Complaint',
            'toffee_val' =>'Toffee',
            'csc_val' =>'Corporate Sales & Complaints'
        );

        $final_array = array();
        foreach($dataset as $data)
        {
                //echo '<br/>cat:'.$data['CATEGORY'];
                //echo '$index:'.
                $index = array_search($data['CATEGORY'],$keyMappings);//Returns index or else false
                
                if($index != FALSE)
                {
                    if((($ticket_category[$index]['tar']*$targetDelta)- $ticket_category[$index]['ach']) > 0)
                    {
                        $ticket_category[$index]['ach']++;
                        $final_array[] = $data['tableID'];
                    }
                }
            //dd();
        }

        //dd($final_array);
        $this->printR($ticket_category,0);
        // $this->printR($final_array,0);

        return $final_array;
    }

    public function getStatusOnly($dataset, $ticket_status, $targetDelta = 1)
    {

        $keyMappings = array(
            'ts_ass_val' =>'Assigned',
            'ts_tf_val' =>'Technical Feedback',
            'ts_re_val' =>'Re Assigned',
            'ts_fl_val' =>'Follow Up',
            'ts_cl_val' =>'Closed'
        );

        $final_array = array();

        foreach($dataset as $data)
        {
            $index = array_search($data['STATUS'],$keyMappings);//Returns index or else false

            if($index != FALSE)
            {
                if((($ticket_status[$index]['tar']*$targetDelta)- $ticket_status[$index]['ach']) > 0)
                {
                        $ticket_status[$index]['ach']++;
                        $final_array[] = $data['tableID'];
                }
            }
        }

        $this->printR($ticket_status,0);
        // $this->printR($final_array,0);

        return $final_array;

    }

    public function getChannelOnly_old($dataset, $channel, $targetDelta = 1)
    {
        $this->printR($channel,0);

        $keyMappings = array(
            'ch_inb_val' =>'Contact Center', //Inbound
            'ch_app_val' =>'BOS-My BL App', //BOS-My BL App
            'ch_ivr_val' =>'Complaint IVR 158',//IVR 158
            'ch_ussd_val' =>'USSD', //USSD
            'ch_mono_val' =>'Monobrand',//Monobrand
            'ch_oth_val' => ''
            //array('120 SMS','Banglalink Service Point','BTRC CRM','Corporate','Corporate Care Portal','Employee','Facebook','Icon','Regional','Retailer App') //What is other here? Need to know
        );

        $final_array = array();

        foreach($dataset as $data)
        {
            echo '<br/>SOURCE:'.$data['SOURCE'];

            $index = array_search($data['SOURCE'],$keyMappings);//Returns index or else false

            if($index != FALSE)
            {
                if((($channel[$index]['tar']*$targetDelta)- $channel[$index]['ach']) > 0)
                {
                        $channel[$index]['ach']++;
                        $final_array[] = $data['tableID'];
                }
            }
        }

        $this->printR($channel,1);
        // $this->printR($final_array,0);

        return $final_array;
    }

    public function getChannelOnly($dataset, $channel, $targetDelta = 1)
    {
        //$this->printR($channel,0);

        $keyMappings = array(
            'ch_inb_val' =>'Contact Center',
            'ch_app_val' =>'BOS-My BL App',
            'ch_ivr_val' =>'Complaint IVR 158',
            'ch_ussd_val' =>'USSD',
            'ch_mono_val' =>'Monobrand',
            'ch_oth_val' => array('120 SMS','Banglalink Service Point','BTRC CRM','Corporate','Corporate Care Portal','Employee','Facebook','Icon','Regional','Retailer App')
        );
        
        $final_array = array();
        
        foreach ($dataset as $data) {
            $index = null;

            //echo '<br/>SOURCE:'.$data['SOURCE'];

            foreach ($keyMappings as $key => $value) {

                if (is_array($value) && in_array($data['SOURCE'], $value)) {
                    $index = $key;
                    break;
                } elseif ($value === $data['SOURCE']) {
                    $index = $key;
                    break;
                }
            }

            //echo '$index:'.$index;
        
            if($index !== null) {
                //$channelIndex = $keyMappings[$index];
                //$targetDelta = 1; // Assuming targetDelta is 1, adjust as needed
        
                if (($channel[$index]['tar'] * $targetDelta) - $channel[$index]['ach'] > 0) {
                    $channel[$index]['ach']++;
                    $final_array[] = $data['tableID'];
                }
            }
        }

        $this->printR($channel,0);
        //$this->printR($final_array,0);

        return $final_array;
    }

    public function getSelectedDataWithIndex($source, $target)
    {
        $final_array = array();

        foreach($source as $s){
            if(array_key_exists($s,$target))
            {
                $final_array[] = $target[$s];
            }
        }

        //$this->printR($final_array, 1);

        return $final_array;
    }


    public function makeTicketCategory($request)
    {
        $nqc_val = $request->{'nqc-val'};
        $oth_val = $request->{'oth-val'};
        $src_val = $request->{'src-val'};
        $prc_val = $request->{'prc-val'};
        $irc_val = $request->{'irc-val'};
        $pcc_val = $request->{'pcc-val'};
        $vasc_val = $request->{'vasc-val'};
        $sr_val = $request->{'sr-val'};
        $rcc_val = $request->{'rcc-val'};
        $pbc_val = $request->{'pbc-val'};
        $toffee_val = $request->{'toffee-val'};
        $csc_val = $request->{'csc-val'};

        return $ticket_category = array(
            'nqc_val' => array('tar'=>$nqc_val,'ach'=>0),
	        'oth_val' => array('tar'=>$oth_val,'ach'=>0),
	        'src_val' => array('tar'=>$src_val,'ach'=>0),
	        'prc_val' => array('tar'=>$prc_val,'ach'=>0),
	        'irc_val' => array('tar'=>$irc_val,'ach'=>0),

            'pcc_val' => array('tar'=>$pcc_val,'ach'=>0),
            'vasc_val' => array('tar'=>$vasc_val,'ach'=>0),
            'sr_val' => array('tar'=>$sr_val,'ach'=>0),
            'rcc_val' => array('tar'=>$rcc_val,'ach'=>0),
            'pbc_val' => array('tar'=>$pbc_val,'ach'=>0),
            'toffee_val' => array('tar'=>$toffee_val,'ach'=>0),
            'csc_val' => array('tar'=>$csc_val,'ach'=>0)
        );
    }

    public function makeTicketStatus($request)
    {
        return $ticket_status = array(
            'ts_ass_val' => array('tar'=>$request->{'ts-ass-val'} ,'ach'=>0),
            'ts_tf_val' => array('tar'=>$request->{'ts-tf-val'} ,'ach'=>0),
            'ts_re_val' => array('tar'=>$request->{'ts-re-val'} ,'ach'=>0),
            'ts_fl_val' => array('tar'=>$request->{'ts-fl-val'} ,'ach'=>0),
            'ts_cl_val' => array('tar'=>$request->{'ts-cl-val'} ,'ach'=>0)
        );
    }
    
    public function makeChannel($request)
    {
        return $channel = array(
            'ch_inb_val' => array('tar'=>$request->{'ch-inb-val'}, 'ach'=>0),
            'ch_app_val' => array('tar'=>$request->{'ch-app-val'}, 'ach'=>0),
            'ch_ivr_val' => array('tar'=>$request->{'ch-ivr-val'}, 'ach'=>0),
            'ch_ussd_val' => array('tar'=>$request->{'ch-ussd-val'}, 'ach'=>0),
            'ch_mono_val' => array('tar'=>$request->{'ch-mono-val'}, 'ach'=>0),
            'ch_oth_val' => array('tar'=>$request->{'ch-oth-val'}, 'ach'=>0)
        );
    }

    public function getFlagArray($request)
    {

        $category_flag = 0;
        $status_flag = 0;
        $channel_flag = 0;

        if(isset($request->{'set-Ticket-Category'}))
            $category_flag = 1;
        
        if(isset($request->{'set-Ticket-Status'}))
            $status_flag = 1;

        if(isset($request->{'set-Channel'}))
            $channel_flag = 1;

        return $flagArray = array(
                    'category_flag' => $category_flag,
                    'status_flag' => $status_flag,
                    'channel_flag' => $channel_flag
                );
    }

    public function AssignToUsers(Request $request)
    {
        //dd($request);
        set_time_limit(-1);

        $callIDs = SamplingModelOutbound::GetCallIDs();
        $totalCalls = sizeof($callIDs);

        //$fixedCalls = $request->fixedCalls;
        $selectedUsers = $request->users;
        $selectedUsersValues = $request->user_values;
        $isRandom = $request->isRandom;
        //$sampleHistoryID = $request->sampleHistoryID;

        //If $selectedUsersValues is not given then it will be distributed equally
        $selectedUsers = $this->getSelectedUsersFromRequest($selectedUsers, $selectedUsersValues, $totalCalls, $isRandom);
        $totalUsers = sizeof($selectedUsers);
        //$this->printR($selectedUsers,1);
        //$users = SamplingModelOutbound::GetUserListByUserID($selectedUsers);

        
        if($totalCalls == 0)
        {
            return redirect()->route('generate-outbound')->withErrors(['Selected data has been expired, please try again.']);
        }

        $user_with_calls = array();
        // $this->printR($callIDs,0);
        // $this->printR($selectedUsers,0);

        // foreach($selectedUsers as $u)
        // {
        $user_with_CALLS_array = array();
        for($i=0; $i < sizeof($selectedUsers); $i++)
        {
            $noOfAssignedCalls = $selectedUsers[$i]['assignedCalls'];

            $random_callids = array_rand($callIDs, $noOfAssignedCalls);

            if($noOfAssignedCalls == 1)
            {
                $user_with_calls[$selectedUsers[$i]['user']] = array($random_callids);
            }else{
                $user_with_calls[$selectedUsers[$i]['user']] = $random_callids;
            }

            //$this->printR($user_with_calls,0);
            //Now delete the assigned calls from callIDs
            $j = 0;
            foreach($user_with_calls[$selectedUsers[$i]['user']] as $callIDIndex)
            {
                //echo '<br/>$callIDIndex:'. $callIDIndex;

                //$user_with_calls[$selectedUsers[$j]['user']] = $callIDs[$callIDIndex];
                $user_with_CALLS_array[$selectedUsers[$i]['user']][] = $callIDs[$callIDIndex];
                unset($callIDs[$callIDIndex]);
                $j++;
            }

            //echo 'Size of Calls:'.sizeof($callIDs);
        
        }
        //}
        //$this->printR($user_with_CALLS_array,1);

        //Now insert this data in assigned_ib table
        $insertedCallsWithUsers = SamplingModelOutbound::assignCallsToUsers($user_with_CALLS_array);

        //Now update source table with pickupFlag=1
        $results = SamplingModelOutbound::UpdateSourceTable($insertedCallsWithUsers);

        //Delete from booking table
        SamplingModelOutbound::DeleteBookingTable('OB');

        //Update Booking history table with isAssigned status=1
        //$updateBookingHistoryStatus = Sampling::updateBookingHistoryStatus($sampleHistoryID);
         
         //exit;
 
        return redirect()->intended('generate-outbound')->with("success","Total ".count($insertedCallsWithUsers)." calls have been assigned to $totalUsers agents.");

        
    }


    public function getSelectedUsersFromRequest($selectedUsers, $selectedUsersValues, $totalCalls, $isRandom)
    {
        //echo '$totalCalls:'.$totalCalls;

        $users_call_array = array();
        $totalUsers = sizeof($selectedUsers);

        // $this->printR($selectedUsers,0);
        // $this->printR($selectedUsersValues,0);

        //If random selected then calculate randomly
        if(isset($isRandom))
        {   

            $eachUser = floor($totalCalls / $totalUsers);
            $remaining = $totalCalls % $totalUsers;

            foreach($selectedUsers as $u)
            {
                
                $selectedUsersValues[$u] = $eachUser;

                // $users_call_array[] = array(
                //                             'user' => $u,
                //                             'assignedCalls' => $eachUser
                //                     );
            }

            //dd($selectedUsersValues);
            //$this->printR($selectedUsersValues,0);

            //If there is remaining
            if($remaining > 0)
            {
                $randomUser = array_rand($selectedUsers,1);
                //dd($randomUser);
                $selectedUsersValues[$selectedUsers[$randomUser]] = $selectedUsersValues[$selectedUsers[$randomUser]] + $remaining;
                //$users_call_array[]
            }

            //$this->printR($selectedUsersValues,1);

        }
        // else
        // {
            foreach($selectedUsers as $u)
            {
                //echo $u;

                // if($request->$u)
                // {
                //     echo 'selected';

                    $users_call_array[] = array(
                                                'user' => $u,
                                                'assignedCalls' => $selectedUsersValues[$u]
                                            );
                    //[$u] = ;
                //}
            }
        //}

        //$this->printR($users_call_array,1);

        return $users_call_array;
    }


    public function getSelectedUsersFromRequest_old($selectedUsers, $selectedUsersValues)
    {
        $users_call_array = array();

        //dd($request);

        foreach($selectedUsers as $u)
        {
            //echo $u;

            // if($request->$u)
            // {
            //     echo 'selected';

                $users_call_array[] = array(
                                            'user' => $u,
                                            'assignedCalls' => $selectedUsersValues[$u]
                                           );
                //[$u] = ;
            //}
        }

        //$this->printR($users_call_array,1);

        return $users_call_array;
    }

    /*
    * Modify below function
    * as per your requirements
    *
    */
    public function makeAgents($objectArray)
    {   
        $agents = array();
        foreach($objectArray as $o)
        {
            $agents[$o->agentid] = $o->cnt;
        }

        return $agents;
    }


    public static function GetAssignedCalls($userid)
    {
        //dd($userid);

        $call = SamplingModelOutbound::GetAssignedCallsFromDB($userid);

        //dd($call);

        return $call;
    }

    public static function UpdateIgnoredCalls($callid, $userid, $reason)
    {
        //dd($reason);

        $rowsAffected = SamplingModelOutbound::UpdateIgnoredCallsInDB($callid, $userid, $reason);

        //dd($call);

        return $rowsAffected;

    }

    public static function UpdateAuditPerAgentByID(Request $request)
    {
        //dd($request);

        $tableID = $request->tableID;
        $newVal = $request->newval;
        

        $updateRange = SamplingModelOutbound::UpdateAuditPerAgentByID($tableID, $newVal);

        if($updateRange)
        {
         $statusMsg = 'Value has been updated successfully.';
        }else{
         $statusMsg = 'Could not update. Please contact with Admin.';
        }
 
        return redirect()->back()->with('status',$statusMsg);

    }

    public static function GetAuditPerAgent($tableID)
    {
        $rawdata = SamplingModelOutbound::getAuditPerAgent($tableID);
        
        return response()->json($rawdata);
    }

    public static function GetAgentCountValidity($agentid, $userid)
    {
        $getUserType = SamplingModelOutbound::getUserTypeAndAuditPerAgent($userid);
        //dd($getUserType);

        $userType = $getUserType[0]->userType;
        $auditPerAgent = $getUserType[0]->value;

        $agentAuditCount = SamplingModelOutbound::getAuditCountByAgents($agentid, $userType);
        //dd($agentAuditCount);
        $totalAuditedCalls = $agentAuditCount[0]->totalAuditedCalls;
        $valid = 0;

        // echo 'totalAuditeCalls'.$totalAuditedCalls;
        // echo 'auditPerAgent'.$auditPerAgent;

        //dd();
        //$valid = 0 means can be audited more or else cannot
        if($totalAuditedCalls>=$auditPerAgent) 
        {
            $valid = 1;
        }

        return $valid;
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
