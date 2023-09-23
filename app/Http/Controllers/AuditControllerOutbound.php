<?php

namespace App\Http\Controllers;

use App\Models\SamplingModelOutbound;
use Illuminate\Http\Request;

class AuditControllerOutbound extends Controller
{
    private static $CategorykeyMappings = array(
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

    private static $StatuskeyMappings = array(
        'ts_ass_val' =>'Assigned',
        'ts_tf_val' =>'Technical Feedback',
        'ts_re_val' =>'Re Assigned',
        'ts_fl_val' =>'Follow Up',
        'ts_cl_val' =>'Closed'
    );

    private static $ChannelkeyMappings = array(
        'ch_inb_val' =>'Contact Center',
        'ch_app_val' =>'BOS-My BL App',
        'ch_ivr_val' =>'Complaint IVR 158',
        'ch_ussd_val' =>'USSD',
        'ch_mono_val' =>'Monobrand',
        'ch_oth_val' => array('120 SMS','Banglalink Service Point','BTRC CRM','Corporate','Corporate Care Portal','Employee','Facebook','Icon','Regional','Retailer App')
    );

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
        $userType = session()->get('userType');
        if(empty($userType))
        {
            return redirect()->route('login')->withErrors(['Session timed out, please login again.']);
        }

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
        $sampleSize = $request->{'no-of-agent'};

        $userType = session()->get('userType'); //BL

        if(empty($userType))
        {
            return redirect()->route('login')->withErrors(['Session timed out, please login again.']);
        }

         //Check if currently anyone is already processing a request in booking table
         //$bookingStatus = SamplingModelOutbound::bookingStatusCheck('OB');
         $bookingStatus = NULL; //Uncomment this line
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
        $secondLayerFlag = $this->getSecondLayerFlag($request);
        //$this->printR($flagArray,0);


        $dataset = $this->getDataSets($from, $to);
        //dd($dataset);

        //Check if dataset is empty or not
        if(empty($dataset)){
            return redirect()->route('generate-outbound')->withErrors(['No data found for mentioned dates. Please try again with different dates.']);
        }

        //Set booking & booking_history table //uncomment below line
        //SamplingModelOutbound::bookingUpdate($assignedBy, 'OB', $from, $to);

        //Make the criteria arrays-
        $ticket_category = $this->makeTicketCategory($request);

        $ticket_status = $this->makeTicketStatus($request);

        $channel = $this->makeChannel($request);

        // $this->printR($ticket_category,0);

        // $this->printR($ticket_status,0);

        // $this->printR($channel,0);

        $category_flag_zero = $status_flag_zero = $channel_flag_zero = false;

        if(is_array($secondLayerFlag))
        {
            $category_flag_zero = ($secondLayerFlag['category_flag_zero'] == 1) ? true : false;
            $status_flag_zero = ($secondLayerFlag['status_flag_zero'] == 1) ? true : false;
            $channel_flag_zero = ($secondLayerFlag['channel_flag_zero'] == 1) ? true : false;
        }

        //Find Agents with Audit Count
        //Not feasible as we do not have any reference for agentID in SuperOffice tickets
        //$agents = $this->makeAgents(SamplingModelOutbound::getAuditCountByAgents($userType));

        $finalArray = $this->SelectSample($dataset, $ticket_category, $ticket_status, $channel, $flagArray, $secondLayerFlag);
        
        //dd($finalArray);

        if((sizeof($finalArray['dataset']) > $sampleSize) && ($category_flag_zero || $status_flag_zero || $channel_flag_zero))
        //Will only process below if all 3 combinations are selected
        {
            echo 'I am inside condition:';
            //Apply same algorithm on filtered data
            $dataset2 = $this->getSelectedDataWithIndex($finalArray['dataset'], $dataset);
            $dataset3 = $this->SwapTableIDAndIndex($dataset2);
            //echo '$dataset3:'.sizeof($dataset3);
            //dd($dataset3);

            $UltimateArray = $this->SelectSample($dataset3, $ticket_category, $ticket_status, $channel, $flagArray);//With fixed delta
            $UltimateArrayWithData = $this->getSelectedDataWithIndex($UltimateArray['dataset'], $dataset);

            if($category_flag_zero && $status_flag_zero && $channel_flag_zero)
            { //If all values are custom

                //Passing Full Dataset as $dataset for add/remove
                //$getFinalCategoryCount = $this->getFinalCategoryCount($UltimateArrayWithData, $dataset, $ticket_category, $ticket_status, $channel);
                $getFinalCategoryCount = $this->getFinalAllThreeCount($UltimateArrayWithData, $dataset3, $ticket_category, $ticket_status, $channel);
                //$getFinalCategoryCount = $this->getFinalCategoryCount($dataset3, $dataset3, $ticket_category, $ticket_status, $channel);
                //$getFinalCategoryCountWithData = $this->getSelectedDataWithIndex($getFinalCategoryCount, $dataset);
                 //dd($getFinalCategoryCount);//Sometimes not add,delete is performed hence it is returned as is
                $getFinalCategoryCount = $this->SwapTableIDAndIndex($getFinalCategoryCount);
                //echo 'UltimateArray:';
                //dd($UltimateArray);
                //echo 'Iaminside final logic';
                //echo 'UltimateArray:'.sizeof($UltimateArray);
                //dd($UltimateArray);
                //$this->printR($UltimateArray,1);
                
                //$finalArray = $UltimateArray['dataset']; //$finalArray = $UltimateArray

                $finalArray = array_keys($getFinalCategoryCount);
            }else{

                $getFinalCategoryCount = $this->SwapTableIDAndIndex($UltimateArrayWithData);
                $finalArray = array_keys($getFinalCategoryCount);
                //dd($finalArray);
            }

        }

        if(array_key_exists('dataset', $finalArray))
        {
            $finalArray = $finalArray['dataset'];
        }
        
        //Record the output size vs audit target and get the Last insert ID
        $sampleHistoryID = SamplingModelOutbound::insertSamplingHistory($assignedBy, $request->{'no-of-agent'},count($finalArray));

        //Take the data from temporary table
        $selectedSample = SamplingModelOutbound::getSelectedSample($finalArray); // //$finalArray['dataset']

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

    public function SelectSample($dataset, $ticket_category, $ticket_status, $channel, $flagArray, $secondLayerFlag = null)
    {       
            $delta_Global = 1000;
            // [category_flag] => 1
            // [status_flag] => 1
            // [channel_flag] => 1
            //echo '<br/>Dataset:'.sizeof($dataset);
            //$this->printR($dataset,0);

            // $this->printR($ticket_category,0);

            // $this->printR($ticket_status,0);

            // $this->printR($channel,0);
            //dd($flagArray);

            //$this->printR($secondLayerFlag,1);
            $category_flag_zero = $status_flag_zero = $channel_flag_zero = false;

            if(is_array($secondLayerFlag))
            {
                $category_flag_zero = ($secondLayerFlag['category_flag_zero'] == 1) ? true : false;
                $status_flag_zero = ($secondLayerFlag['status_flag_zero'] == 1) ? true : false;
                $channel_flag_zero = ($secondLayerFlag['channel_flag_zero'] == 1) ? true : false;
            }
            
            
            $category_flag = ($flagArray['category_flag'] == 1) ? 1 : 0;
            $status_flag = ($flagArray['status_flag'] == 1) ? 2 : 0;
            $channel_flag = ($flagArray['channel_flag'] == 1) ? 4 : 0;

            //Getting BIT wise operator
            $combination = $category_flag | $status_flag | $channel_flag;
            echo '$combination:'.$combination;

            switch($combination){
                case 0: //We have validated this in UI
                    //echo "no choices selected";
                    
                    return redirect()->route('generate-outbound')->withErrors(['No criteria selected. Please select atleast one criteria.']);
                    break;
                case 1:
                    //echo "category_flag selected.";

                    $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category,1);
                    
                    return array('dataset' => $CategoryOnlyArray['final_array']);

                    break;
                case 2:
                    //echo "status_flag selected.";
                    $StatusOnlyArray = $this->getStatusOnly($dataset, $ticket_status, 1);

                    return array('dataset' => $StatusOnlyArray['final_array']);

                    break;
                case 3:
                    //echo "category_flag | status_flag selected.";
                    $detla_Category = 4;
                    $delta_Status = 1;

                    //if second layer flag is set
                    if($category_flag_zero && $status_flag_zero)
                    {
                        //echo 'I am in second layer flag';
                        // $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, 1);
                        // $StatusOnlyArray = $this->getStatusOnly($dataset, $ticket_status, 1);
                        // $merge = array_merge($CategoryOnlyArray, $StatusOnlyArray);
                        // //echo 'MergeSize: '.sizeof($merge);
                        // $unique = array_unique($merge);
                        // //echo 'UniqueSize: '.sizeof($unique);
                        // return $unique;
                        // break;
                        $detla_Category = $delta_Status = $delta_Global;
                    }else if($category_flag_zero){
                        $detla_Category = $delta_Global;

                    }else if($status_flag_zero){
                        $detla_Category = 10; //As we have custom % in second layer(Status)
                        $delta_Status = $delta_Global;
                    }

                    //First get categories with delta
                    $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, $detla_Category); //2
                    //echo '<br/>CategoryOnlyArray:'.sizeof($CategoryOnlyArray);
                    $CategoryOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryOnlyArray['final_array'], $dataset);


                    $CategoryAndStatusArray = $this->getStatusOnly($CategoryOnlyArrayWithDataset, $ticket_status, $delta_Status); //1
                    //echo '<br/>CategoryAndStatusArray:'.sizeof($CategoryAndStatusArray);

                    //$this->printR($CategoryAndStatusArray,1);

                    //return $CategoryAndStatusArray;

                    return array('dataset' => $CategoryAndStatusArray['final_array']);

                    break;

                case 4:
                    //echo "channel_flag selected.";
                    $ChannelOnlyArray = $this->getChannelOnly($dataset, $channel, 1);
                    //return $ChannelOnlyArray;

                    return array('dataset' => $ChannelOnlyArray['final_array']);
                    
                    break;

                case 5:
                    //echo "category_flag and channel_flag selected.";
                    //Default deltas
                    $detla_Category = 4;
                    $delta_Channel = 1;

                    if($category_flag_zero && $channel_flag_zero)
                    {
                        // $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, 1);    
                        // $ChannelOnlyArray = $this->getChannelOnly($dataset, $channel, 1);
                        // $merge = array_merge($CategoryOnlyArray, $ChannelOnlyArray);
                        // $unique = array_unique($merge);
                        // return $unique;
                        // break;
                        $detla_Category = $delta_Channel = $delta_Global;
                    }else if($category_flag_zero){
                        $detla_Category = $delta_Global;

                    }else if($channel_flag_zero){
                        $detla_Category = 10; //As we have custom % in second layer(Channel)
                        $delta_Channel = $delta_Global;
                    }

                    //First get categories with delta
                    $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, $detla_Category); //2

                    $CategoryOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryOnlyArray['final_array'], $dataset);

                    $CategoryAndChannelArray = $this->getChannelOnly($CategoryOnlyArrayWithDataset, $channel, $delta_Channel); //1

                    //echo '<br/>CategoryAndChannelArray:'.sizeof($CategoryAndChannelArray);

                    //return $CategoryAndChannelArray;

                    return array('dataset' => $CategoryAndChannelArray['final_array']);

                    break;

                case 6:
                    //echo "status_flag | channel_flag selected.";
                    //Default deltas
                    $delta_Status = 4;
                    $delta_Channel = 1;

                    if($channel_flag_zero && $status_flag_zero)
                    {
                        // $ChannelOnlyArray = $this->getChannelOnly($dataset, $channel, 1);
                        // $StatusOnlyArray = $this->getStatusOnly($dataset, $ticket_status, 1);

                        // $merge = array_merge($ChannelOnlyArray, $StatusOnlyArray);
                        // $unique = array_unique($merge);
                        // return $unique;
                        // break;
                        $delta_Status = $delta_Channel = $delta_Global;

                    }else if($status_flag_zero){
                        $delta_Status = $delta_Global;

                    }else if($channel_flag_zero){
                        $delta_Status = 10; //As we have custom % in second layer(Channel)
                        $delta_Channel = $delta_Global;
                        
                        //Check why only 275 is generated for target-300
                    }

                    
                    //First get the status with delta
                    $StatusOnlyArray = $this->getStatusOnly($dataset, $ticket_status, $delta_Status); //2
                    
                    //dd($StatusOnlyArray);

                    $StatusOnlyArrayWithDataset = $this->getSelectedDataWithIndex($StatusOnlyArray['final_array'], $dataset);

                    $StatusAndChannelArray = $this->getChannelOnly($StatusOnlyArrayWithDataset, $channel, $delta_Channel); //1
                    
                    //return $StatusAndChannelArray;

                    return array('dataset' => $StatusAndChannelArray['final_array']);

                    break;

                case 7:

                    //Default
                    $detla_Category = 4;
                    $delta_Status = 4;
                    $delta_Channel = 1;

                    if($category_flag_zero && $status_flag_zero && $channel_flag_zero)
                    {   
                        //echo 'I am here inside all 0';
                        // $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, 1);
                        // $StatusOnlyArray = $this->getStatusOnly($dataset, $ticket_status, 1);
                        // $ChannelOnlyArray = $this->getChannelOnly($dataset, $channel, 1);

                        // $merge = array_merge($CategoryOnlyArray, $StatusOnlyArray, $ChannelOnlyArray);
                        // $unique = array_unique($merge);
                        // return $unique;

                        //Taking all data for custom %
                        // $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, 1000);
                        // $CategoryOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryOnlyArray, $dataset);
                        // echo '<br/>$CategoryOnlyArray:'.sizeof($CategoryOnlyArray);
                        // $this->printR($CategoryOnlyArray,0);


                        // $CategoryAndStatusArray = $this->getStatusOnly($CategoryOnlyArrayWithDataset, $ticket_status, 1000); //4
                        // $StatusOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryAndStatusArray, $dataset);
                        // echo '<br/>$CategoryAndStatusArray:'.sizeof($CategoryAndStatusArray);
                        // $this->printR($CategoryAndStatusArray,0);


                        // $CategoryAndStatusAndChannelArray = $this->getChannelOnly($StatusOnlyArrayWithDataset, $channel, 1000); //2
                        // echo '<br/>$CategoryAndStatusAndChannelArray:'.sizeof($CategoryAndStatusAndChannelArray);
                        // $this->printR($CategoryAndStatusAndChannelArray,0);
                        // //exit;

                        // return $CategoryAndStatusAndChannelArray;
                        
                        // break;

                        $detla_Category = $delta_Status = $delta_Channel = $delta_Global;

                    }else if($category_flag_zero && $status_flag_zero){

                        $detla_Category = $delta_Status = $delta_Global;

                    }else if($category_flag_zero && $channel_flag_zero){
                        $detla_Category = $delta_Channel = $delta_Global;

                    }else if($category_flag_zero){
                        $detla_Category = $delta_Global;

                    }else if($status_flag_zero && $channel_flag_zero){
                        
                        //PERFOMR TEST
                        $detla_Category = 10; //As we have custom % in second layer(Status & Channel)
                        $delta_Status = $delta_Channel = $delta_Global;
                        //Status count is deviated as priority for Category is first

                    }else if($status_flag_zero){

                        //PERFOMR TEST
                        $detla_Category = 10; //As we have custom % in second layer(Status)
                        $delta_Status = $delta_Global;
                        //$delta_Channel will remain same-1

                    }else if($channel_flag_zero){

                        //PERFOMR TEST //All custom filtered data is showing
                        $detla_Category = 10; //As we have custom % in second layer(Channel)
                        $delta_Status = 10;
                        $delta_Channel = $delta_Global;
                    }
                    

                    //echo "category_flag | status_flag | channel_flag selected.";
                    /*
                    echo '$detla_Category:'.$detla_Category;
                    echo '$delta_Status:'.$delta_Status;
                    echo '$delta_Channel:'.$delta_Channel;
                    */

                    //echo 'dataset in second call:';
                    //$this->printR($dataset,0); //coming
                    
                    //$this->printR($ticket_category,0);

                    //Implementing multiple filter layer with priority
                    $CategoryOnlyArray = $this->getCategoryOnly($dataset, $ticket_category, $detla_Category); //4
                    //$this->printR($CategoryOnlyArray,0);

                    $CategoryOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryOnlyArray['final_array'], $dataset);
                    //echo '$CategoryOnlyArrayWithDataset:'.sizeof($CategoryOnlyArrayWithDataset);
                    //$this->printR($CategoryOnlyArrayWithDataset,0);


                    //Status filtered from category
                    $CategoryAndStatusArray = $this->getStatusOnly($CategoryOnlyArrayWithDataset, $ticket_status, $delta_Status); //4
                    $StatusOnlyArrayWithDataset = $this->getSelectedDataWithIndex($CategoryAndStatusArray['final_array'], $dataset);
                    //echo '$StatusOnlyArrayWithDataset:'.sizeof($StatusOnlyArrayWithDataset);
                    //$this->printR($CategoryAndStatusArray,0);

                    //Channel filtered from Status,Category
                    $CategoryAndStatusAndChannelArray = $this->getChannelOnly($StatusOnlyArrayWithDataset, $channel, $delta_Channel); //1
                    //echo '$CategoryAndStatusAndChannelArray:'.sizeof($CategoryAndStatusAndChannelArray);
                    //$this->printR($CategoryAndStatusAndChannelArray['final_array'],0);

                    //echo 'before second return:';
                    //dd($CategoryAndStatusAndChannelArray);
                    
                    //return $CategoryAndStatusAndChannelArray;

                    //Returning with final counts
                    return array(
                        'dataset' => $CategoryAndStatusAndChannelArray['final_array'],
                        'ticket_category' => $CategoryOnlyArray['ticket_category'],
                        'ticket_status' => $CategoryAndStatusArray['ticket_status'],
                        'channel' => $CategoryAndStatusAndChannelArray['channel']
                    );
                    
                    break;

                    /* Reverse process --> Not practical
                    $channelOnlyArray = $this->getChannelOnly($dataset, $channel, 10);
                    $channelOnlyArrayWithDataset = $this->getSelectedDataWithIndex($channelOnlyArray, $dataset);


                    //Status filtered from Channel
                    $ChannelAndStatusArray = $this->getStatusOnly($channelOnlyArrayWithDataset, $ticket_status, 5);
                    $StatusOnlyArrayWithDataset = $this->getSelectedDataWithIndex($ChannelAndStatusArray, $dataset);


                    //Category filtered from Status
                    $ChannelAndStatusAndCategory = $this->getCategoryOnly($StatusOnlyArrayWithDataset, $ticket_category, 2);

                    return $ChannelAndStatusAndCategory;
                    */
            }

        
        // if($flagArray['category_flag'] == 1)
        // {
        //     $this->GetCategoryOnly();
        // }
    }

    public function getDataSets($from, $to) //, $source = null
    {
        ini_set('memory_limit','1024M');

        // echo "Source:";
        // $this->printR($source,0);

        // if(is_array($source))
        // {
        //     $data = $source;
        // }else{
            $data = SamplingModelOutbound::getDataSetFromTable($from, $to);
        //}
        
        //dd($source);
        
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
                        //'parentID' => $d->parentID //Added purposely
            );
        }

        return $arr;
    }

    public function getCategoryOnly($dataset, $ticket_category, $targetDelta = 1)
    {
        //echo $targetDelta;
        //echo "<br/>----------------CATEGORY START----------------------";
        // print_r($dataset);
        // print_r($ticket_category);

        /*$keyMappings = array(
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
        );*/

        $final_array = array();
        foreach($dataset as $data)
        {
                //echo '<br/>cat:'.$data['CATEGORY'];
                //echo '$index:'.
                $index = array_search($data['CATEGORY'], self::$CategorykeyMappings);//Returns index or else false
                
                if($index != FALSE)
                {
                    if((($ticket_category[$index]['tar']*$targetDelta) - $ticket_category[$index]['ach']) > 0)
                    {
                        $ticket_category[$index]['ach']++;
                        $final_array[] = $data['tableID'];
                    }

                    //getting actual count
                    $ticket_category[$index]['ach_act']++;
                }
            //dd();
        }

        //dd($final_array);
        //$this->printR($ticket_category,0);
        //$this->printR($final_array,0);
        //echo "----------------CATEGORY END----------------------";
        //return $final_array;
        return array(
            'final_array' => $final_array,
            'ticket_category' => $ticket_category
        );
    }

    public function getStatusOnly($dataset, $ticket_status, $targetDelta = 1)
    {
        //echo "<br/>-------------STATUS START------------------------";
        // $keyMappings = array(
        //     'ts_ass_val' =>'Assigned',
        //     'ts_tf_val' =>'Technical Feedback',
        //     'ts_re_val' =>'Re Assigned',
        //     'ts_fl_val' =>'Follow Up',
        //     'ts_cl_val' =>'Closed'
        // );

        $final_array = array();

        foreach($dataset as $data)
        {
            $index = array_search($data['STATUS'], self::$StatuskeyMappings);//Returns index or else false //$keyMappings

            if($index != FALSE)
            {
                if((($ticket_status[$index]['tar']*$targetDelta)- $ticket_status[$index]['ach']) > 0)
                {
                        $ticket_status[$index]['ach']++;
                        $final_array[] = $data['tableID'];
                }

                $ticket_status[$index]['ach_act']++;

            }
        }

        //$this->printR($ticket_status,0);
        //$this->printR($final_array,0);

        //echo "-------------STATUS END------------------------";
        //return $final_array;

        return array(
            'final_array' => $final_array,
            'ticket_status' => $ticket_status
        );

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
        //echo "<br/>-----------------CHANNEL START-----------------------";
        // $keyMappings = array(
        //     'ch_inb_val' =>'Contact Center',
        //     'ch_app_val' =>'BOS-My BL App',
        //     'ch_ivr_val' =>'Complaint IVR 158',
        //     'ch_ussd_val' =>'USSD',
        //     'ch_mono_val' =>'Monobrand',
        //     'ch_oth_val' => array('120 SMS','Banglalink Service Point','BTRC CRM','Corporate','Corporate Care Portal','Employee','Facebook','Icon','Regional','Retailer App')
        // );
        
        $final_array = array();
        
        foreach ($dataset as $data) {
            $index = null;

            //echo '<br/>SOURCE:'.$data['SOURCE'];

            foreach (self::$ChannelkeyMappings as $key => $value) { //$keyMappings

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

                $channel[$index]['ach_act']++;
            }
        }

        //$this->printR($channel,0);
        //$this->printR($final_array,0);
        //echo "-----------------CHANNEL END-----------------------";
        
        //return $final_array;

        return array(
            'final_array' => $final_array,
            'channel' => $channel
        );
    }

    public function getSelectedDataWithIndex($source, $target)
    {

        $final_array = array();

        foreach($source as $s){
            if(array_key_exists($s, $target))
            {
                $final_array[] = $target[$s];
            }
        }

        //$this->printR($final_array, 1);

        return $final_array;
    }

    public function getFinalAllThreeCount($dataset, $fullDataset, $ticket_category, $ticket_status, $channel)
    {
        //$this->getSelectedDataWithIndex();
        echo 'I am inside getFinalCategoryCount';
        //dd($dataset);
        
        //$this->printR($ticket_category, 0);
        //$this->printR($dataset, 0);
        //$this->printR($fullDataset, 0);

        //Getting counts only
        $ticket_category_ach = $this->getCategoryOnly($dataset, $ticket_category, $targetDelta = 1);
        $ticket_category_ach = $ticket_category_ach['ticket_category'];
        //$ticket_category = $ticket_category_ach['ticket_category'];

        echo '<br/>initial achieve:';
        $this->printR($ticket_category_ach, 0);

        $ticket_status_ach = $this->getStatusOnly($dataset, $ticket_status, $targetDelta = 1);
        $ticket_status_ach = $ticket_status_ach['ticket_status'];
        //$this->printR($ticket_status,0);


        $channel_ach = $this->getChannelOnly($dataset, $channel, $targetDelta = 1);
        $channel_ach = $channel_ach['channel'];
        //$this->printR($channel_ach,0);
        
        $modifiedDataset = $dataset;

        $delta_per = 0.05;
        $loopCount = 0;
        
        while(
          //Ticket Categories
            ($ticket_category_ach['nqc_val']['tar'] > 0 && (abs($ticket_category_ach['nqc_val']['tar']-$ticket_category_ach['nqc_val']['ach_act'])/$ticket_category_ach['nqc_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['oth_val']['tar'] > 0 && (abs($ticket_category_ach['oth_val']['tar']-$ticket_category_ach['oth_val']['ach_act'])/$ticket_category_ach['oth_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['src_val']['tar'] > 0 && (abs($ticket_category_ach['src_val']['tar']-$ticket_category_ach['src_val']['ach_act'])/$ticket_category_ach['src_val']['tar']) >= $delta_per)   
          || ($ticket_category_ach['prc_val']['tar'] > 0 && (abs($ticket_category_ach['prc_val']['tar']-$ticket_category_ach['prc_val']['ach_act'])/$ticket_category_ach['prc_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['irc_val']['tar'] > 0 && (abs($ticket_category_ach['irc_val']['tar']-$ticket_category_ach['irc_val']['ach_act'])/$ticket_category_ach['irc_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['pcc_val']['tar'] > 0 && (abs($ticket_category_ach['pcc_val']['tar']-$ticket_category_ach['pcc_val']['ach_act'])/$ticket_category_ach['pcc_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['vasc_val']['tar'] > 0 && (abs($ticket_category_ach['vasc_val']['tar']-$ticket_category_ach['vasc_val']['ach_act'])/$ticket_category_ach['vasc_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['sr_val']['tar'] > 0 && (abs($ticket_category_ach['sr_val']['tar'] - $ticket_category_ach['sr_val']['ach_act']) / $ticket_category_ach['sr_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['rcc_val']['tar'] > 0 && (abs($ticket_category_ach['rcc_val']['tar'] - $ticket_category_ach['rcc_val']['ach_act']) / $ticket_category_ach['rcc_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['pbc_val']['tar'] > 0 && (abs($ticket_category_ach['pbc_val']['tar'] - $ticket_category_ach['pbc_val']['ach_act']) / $ticket_category_ach['pbc_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['toffee_val']['tar'] > 0 && (abs($ticket_category_ach['toffee_val']['tar'] - $ticket_category_ach['toffee_val']['ach_act']) / $ticket_category_ach['toffee_val']['tar']) >= $delta_per)
          || ($ticket_category_ach['csc_val']['tar'] > 0 && (abs($ticket_category_ach['csc_val']['tar'] - $ticket_category_ach['csc_val']['ach_act']) / $ticket_category_ach['csc_val']['tar']) >= $delta_per)
          
          //Ticket Status
          || ($ticket_status_ach['ts_ass_val']['tar'] > 0 && (abs($ticket_status_ach['ts_ass_val']['tar'] - $ticket_status_ach['ts_ass_val']['ach_act']) / $ticket_status_ach['ts_ass_val']['tar']) >= $delta_per)
          || ($ticket_status_ach['ts_fl_val']['tar'] > 0 && (abs($ticket_status_ach['ts_fl_val']['tar']-$ticket_status_ach['ts_fl_val']['ach_act'])/$ticket_status_ach['ts_fl_val']['tar']) >= $delta_per)      
          || ($ticket_status_ach['ts_re_val']['tar'] > 0 && (abs($ticket_status_ach['ts_re_val']['tar'] - $ticket_status_ach['ts_re_val']['ach_act']) / $ticket_status_ach['ts_re_val']['tar']) >= $delta_per)
          || ($ticket_status_ach['ts_tf_val']['tar'] > 0 && (abs($ticket_status_ach['ts_tf_val']['tar'] - $ticket_status_ach['ts_tf_val']['ach_act']) / $ticket_status_ach['ts_tf_val']['tar']) >= $delta_per)
          || ($ticket_status_ach['ts_cl_val']['tar'] > 0 && (abs($ticket_status_ach['ts_cl_val']['tar']-$ticket_status_ach['ts_cl_val']['ach_act'])/$ticket_status_ach['ts_cl_val']['tar']) >= $delta_per)      
          
          //Channel
          || ($channel_ach['ch_inb_val']['tar'] > 0 && (abs($channel_ach['ch_inb_val']['tar']-$channel_ach['ch_inb_val']['ach_act'])/$channel_ach['ch_inb_val']['tar']) >= $delta_per)      
          || ($channel_ach['ch_app_val']['tar'] > 0 && (abs($channel_ach['ch_app_val']['tar'] - $channel_ach['ch_app_val']['ach_act']) / $channel_ach['ch_app_val']['tar']) >= $delta_per)
          || ($channel_ach['ch_ivr_val']['tar'] > 0 && (abs($channel_ach['ch_ivr_val']['tar'] - $channel_ach['ch_ivr_val']['ach_act']) / $channel_ach['ch_ivr_val']['tar']) >= $delta_per)        
          || ($channel_ach['ch_ussd_val']['tar'] > 0 && (abs($channel_ach['ch_ussd_val']['tar'] - $channel_ach['ch_ussd_val']['ach_act']) / $channel_ach['ch_ussd_val']['tar']) >= $delta_per)
          || ($channel_ach['ch_mono_val']['tar'] > 0 && (abs($channel_ach['ch_mono_val']['tar'] - $channel_ach['ch_mono_val']['ach_act']) / $channel_ach['ch_mono_val']['tar']) >= $delta_per)
          || ($channel_ach['ch_oth_val']['tar'] > 0 && (abs($channel_ach['ch_oth_val']['tar'] - $channel_ach['ch_oth_val']['ach_act']) / $channel_ach['ch_oth_val']['tar']) >= $delta_per)
            
            // Conditions for 'ch_oth_val' (an array of values)
            /*(
                is_array($channel_ach['ch_oth_val'])
                && count(array_intersect($channel_ach['ch_oth_val'], $targetValues['ch_oth_val'])) > 0
            )*/
        
        
          //  || ($ticket_category['ch_inb_val']['tar'] > 0 && (abs($ticket_category['ch_inb_val']['tar']-$ticket_category['ch_inb_val']['ach_act'])/$ticket_category['ch_inb_val']['tar']) >= $delta_per)
            // ($ticket_category['nqc']['tar']-$ticket_category['nqc']['ach_act'] != 0 
            // && $ticket_category['nqc']['tar']-$ticket_category['nqc']['ach_act'] 
        )
        {
            echo "----------------------START loopcount: ".($loopCount+1)."--------------------------------";
            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';
            //$this->printR($ticket_category_ach, 0);


            if($ticket_category_ach['nqc_val']['tar'] > 0 && (abs($ticket_category_ach['nqc_val']['tar']-$ticket_category_ach['nqc_val']['ach_act'])/$ticket_category_ach['nqc_val']['tar']) >= $delta_per)
            {
                if($ticket_category_ach['nqc_val']['tar'] > $ticket_category_ach['nqc_val']['ach_act'])
                { 
                    //Need to add
                    $addCount = $ticket_category_ach['nqc_val']['tar'] - $ticket_category_ach['nqc_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|nqc_val', $addCount, TRUE, $additionalFlag);

                }else{
                    //Need to remove
                    $removeCount = $ticket_category_ach['nqc_val']['ach_act'] - $ticket_category_ach['nqc_val']['tar'] ;
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|nqc_val', $removeCount, FALSE, $additionalFlag);
                    //exit;
                    //$ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1);
                    //echo 'Final Ticket Category:';
                    //$this->printR(($ticket_category_ach['ticket_category']), 1);
                }
            }

            //Now get the modified counts
            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';
            //$this->printR($ticket_category_ach, 0);

            if($ticket_category_ach['oth_val']['tar'] > 0 && (abs($ticket_category_ach['oth_val']['tar']-$ticket_category_ach['oth_val']['ach_act'])/$ticket_category_ach['oth_val']['tar']) >= $delta_per)
            {
                if($ticket_category_ach['oth_val']['tar'] > $ticket_category_ach['oth_val']['ach_act'])
                { 
                    //Need to add
                    $addCount = $ticket_category_ach['oth_val']['tar'] - $ticket_category_ach['oth_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|oth_val', $addCount, TRUE, $additionalFlag);

                }else{
                    //Need to remove
                    $removeCount = $ticket_category_ach['oth_val']['ach_act'] - $ticket_category_ach['oth_val']['tar'] ;
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|oth_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';
            //$this->printR($ticket_category_ach, 0);

            if($ticket_category_ach['src_val']['tar'] > 0 && (abs($ticket_category_ach['src_val']['tar']-$ticket_category_ach['src_val']['ach_act'])/$ticket_category_ach['src_val']['tar']) >= $delta_per)
            {
                if($ticket_category_ach['src_val']['tar'] > $ticket_category_ach['src_val']['ach_act'])
                { 
                    //Need to add
                    $addCount = $ticket_category_ach['src_val']['tar'] - $ticket_category_ach['src_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|src_val', $addCount, TRUE, $additionalFlag);

                }else{
                    //Need to remove
                    $removeCount = $ticket_category_ach['src_val']['ach_act'] - $ticket_category_ach['src_val']['tar'] ;
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|src_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';
            //$this->printR($ticket_category_ach, 0);

            if($ticket_category_ach['prc_val']['tar'] > 0 && (abs($ticket_category_ach['prc_val']['tar']-$ticket_category_ach['prc_val']['ach_act'])/$ticket_category_ach['prc_val']['tar']) >= $delta_per)
            {
                if($ticket_category_ach['prc_val']['tar'] > $ticket_category_ach['prc_val']['ach_act'])
                { 
                    //Need to add
                    $addCount = $ticket_category_ach['prc_val']['tar'] - $ticket_category_ach['prc_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|prc_val', $addCount, TRUE, $additionalFlag);

                }else{
                    //Need to remove
                    $removeCount = $ticket_category_ach['prc_val']['ach_act'] - $ticket_category_ach['prc_val']['tar'] ;
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|prc_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';

            if($ticket_category_ach['irc_val']['tar'] > 0 && (abs($ticket_category_ach['irc_val']['tar']-$ticket_category_ach['irc_val']['ach_act'])/$ticket_category_ach['irc_val']['tar']) >= $delta_per)
            {
                if($ticket_category_ach['irc_val']['tar'] > $ticket_category_ach['irc_val']['ach_act'])
                { 
                    //Need to add
                    $addCount = $ticket_category_ach['irc_val']['tar'] - $ticket_category_ach['irc_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|irc_val', $addCount, TRUE, $additionalFlag);

                }else{
                    //Need to remove
                    $removeCount = $ticket_category_ach['irc_val']['ach_act'] - $ticket_category_ach['irc_val']['tar'] ;
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|irc_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';

            if($ticket_category_ach['pcc_val']['tar'] > 0 && (abs($ticket_category_ach['pcc_val']['tar']-$ticket_category_ach['pcc_val']['ach_act'])/$ticket_category_ach['pcc_val']['tar']) >= $delta_per)
            {
                if($ticket_category_ach['pcc_val']['tar'] > $ticket_category_ach['pcc_val']['ach_act'])
                { 
                    //Need to add
                    $addCount = $ticket_category_ach['pcc_val']['tar'] - $ticket_category_ach['pcc_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|pcc_val', $addCount, TRUE, $additionalFlag);

                }else{
                    //Need to remove
                    $removeCount = $ticket_category_ach['pcc_val']['ach_act'] - $ticket_category_ach['pcc_val']['tar'] ;
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|pcc_val', $removeCount, FALSE, $additionalFlag);
                }
            }


            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';

            if($ticket_category_ach['vasc_val']['tar'] > 0 && (abs($ticket_category_ach['vasc_val']['tar']-$ticket_category_ach['vasc_val']['ach_act'])/$ticket_category_ach['vasc_val']['tar']) >= $delta_per)
            {
                if($ticket_category_ach['vasc_val']['tar'] > $ticket_category_ach['vasc_val']['ach_act'])
                { 
                    //Need to add
                    $addCount = $ticket_category_ach['vasc_val']['tar'] - $ticket_category_ach['vasc_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|vasc_val', $addCount, TRUE, $additionalFlag);

                }else{
                    //Need to remove
                    $removeCount = $ticket_category_ach['vasc_val']['ach_act'] - $ticket_category_ach['vasc_val']['tar'] ;
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|vasc_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';

            // Conditions for 'sr_val'
            if ($ticket_category_ach['sr_val']['tar'] > 0 && (abs($ticket_category_ach['sr_val']['tar'] - $ticket_category_ach['sr_val']['ach_act']) / $ticket_category_ach['sr_val']['tar']) >= $delta_per) {
                if ($ticket_category_ach['sr_val']['tar'] > $ticket_category_ach['sr_val']['ach_act']) {
                    // Need to add
                    $addCount = $ticket_category_ach['sr_val']['tar'] - $ticket_category_ach['sr_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|sr_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $ticket_category_ach['sr_val']['ach_act'] - $ticket_category_ach['sr_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|sr_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';

            // Conditions for 'rcc_val'
            if ($ticket_category_ach['rcc_val']['tar'] > 0 && (abs($ticket_category_ach['rcc_val']['tar'] - $ticket_category_ach['rcc_val']['ach_act']) / $ticket_category_ach['rcc_val']['tar']) >= $delta_per) {
                if ($ticket_category_ach['rcc_val']['tar'] > $ticket_category_ach['rcc_val']['ach_act']) {
                    // Need to add
                    $addCount = $ticket_category_ach['rcc_val']['tar'] - $ticket_category_ach['rcc_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|rcc_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $ticket_category_ach['rcc_val']['ach_act'] - $ticket_category_ach['rcc_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|rcc_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';

            // Conditions for 'pbc_val'
            if ($ticket_category_ach['pbc_val']['tar'] > 0 && (abs($ticket_category_ach['pbc_val']['tar'] - $ticket_category_ach['pbc_val']['ach_act']) / $ticket_category_ach['pbc_val']['tar']) >= $delta_per) {
                if ($ticket_category_ach['pbc_val']['tar'] > $ticket_category_ach['pbc_val']['ach_act']) {
                    // Need to add
                    $addCount = $ticket_category_ach['pbc_val']['tar'] - $ticket_category_ach['pbc_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|pbc_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $ticket_category_ach['pbc_val']['ach_act'] - $ticket_category_ach['pbc_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|pbc_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';

            // Conditions for 'toffee_val'
            if ($ticket_category_ach['toffee_val']['tar'] > 0 && (abs($ticket_category_ach['toffee_val']['tar'] - $ticket_category_ach['toffee_val']['ach_act']) / $ticket_category_ach['toffee_val']['tar']) >= $delta_per) {
                if ($ticket_category_ach['toffee_val']['tar'] > $ticket_category_ach['toffee_val']['ach_act']) {
                    // Need to add
                    $addCount = $ticket_category_ach['toffee_val']['tar'] - $ticket_category_ach['toffee_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|toffee_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $ticket_category_ach['toffee_val']['ach_act'] - $ticket_category_ach['toffee_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|toffee_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            //echo '<br/>ticket_category_ach:';

            // Conditions for 'csc_val'
            if ($ticket_category_ach['csc_val']['tar'] > 0 && (abs($ticket_category_ach['csc_val']['tar'] - $ticket_category_ach['csc_val']['ach_act']) / $ticket_category_ach['csc_val']['tar']) >= $delta_per) {
                if ($ticket_category_ach['csc_val']['tar'] > $ticket_category_ach['csc_val']['ach_act']) {
                    // Need to add
                    $addCount = $ticket_category_ach['csc_val']['tar'] - $ticket_category_ach['csc_val']['ach_act'];
                    $additionalFlag = array('ticket_status_ach'=>$ticket_status_ach, 'channel_ach'=>$channel_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|csc_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $ticket_category_ach['csc_val']['ach_act'] - $ticket_category_ach['csc_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'CATEGORY|csc_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            $this->printR($ticket_category_ach, 0);

            
                    //STATUS check
                    $ticket_status_ach = $this->getStatusOnly($modifiedDataset, $ticket_status, $targetDelta = 1);
                    $ticket_status_ach = $ticket_status_ach['ticket_status'];
                    
                    // Conditions for 'ts_ass_val'
                    if ($ticket_status_ach['ts_ass_val']['tar'] > 0 && (abs($ticket_status_ach['ts_ass_val']['tar'] - $ticket_status_ach['ts_ass_val']['ach_act']) / $ticket_status_ach['ts_ass_val']['tar']) >= $delta_per) {
                        if ($ticket_status_ach['ts_ass_val']['tar'] > $ticket_status_ach['ts_ass_val']['ach_act']) {
                            // Need to add
                            $addCount = $ticket_status_ach['ts_ass_val']['tar'] - $ticket_status_ach['ts_ass_val']['ach_act'];
                            $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'channel_ach'=>$channel_ach);
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_ass_val', $addCount, TRUE, $additionalFlag);
                        } else {
                            // Need to remove
                            $removeCount = $ticket_status_ach['ts_ass_val']['ach_act'] - $ticket_status_ach['ts_ass_val']['tar'];
                            $additionalFlag = NULL;
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_ass_val', $removeCount, FALSE, $additionalFlag);
                        }
                    }

                    $ticket_status_ach = $this->getStatusOnly($modifiedDataset, $ticket_status, $targetDelta = 1);
                    $ticket_status_ach = $ticket_status_ach['ticket_status'];

                    // Conditions for 'ts_tf_val'
                    if ($ticket_status_ach['ts_tf_val']['tar'] > 0 && (abs($ticket_status_ach['ts_tf_val']['tar'] - $ticket_status_ach['ts_tf_val']['ach_act']) / $ticket_status_ach['ts_tf_val']['tar']) >= $delta_per) {
                        if ($ticket_status_ach['ts_tf_val']['tar'] > $ticket_status_ach['ts_tf_val']['ach_act']) {
                            // Need to add
                            $addCount = $ticket_status_ach['ts_tf_val']['tar'] - $ticket_status_ach['ts_tf_val']['ach_act'];
                            $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'channel_ach'=>$channel_ach);
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_tf_val', $addCount, TRUE, $additionalFlag);
                        } else {
                            // Need to remove
                            $removeCount = $ticket_status_ach['ts_tf_val']['ach_act'] - $ticket_status_ach['ts_tf_val']['tar'];
                            $additionalFlag = NULL;
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_tf_val', $removeCount, FALSE, $additionalFlag);
                        }
                    }

                    $ticket_status_ach = $this->getStatusOnly($modifiedDataset, $ticket_status, $targetDelta = 1);
                    $ticket_status_ach = $ticket_status_ach['ticket_status'];

                    // Conditions for 'ts_re_val'
                    if ($ticket_status_ach['ts_re_val']['tar'] > 0 && (abs($ticket_status_ach['ts_re_val']['tar'] - $ticket_status_ach['ts_re_val']['ach_act']) / $ticket_status_ach['ts_re_val']['tar']) >= $delta_per) {
                        if ($ticket_status_ach['ts_re_val']['tar'] > $ticket_status_ach['ts_re_val']['ach_act']) {
                            // Need to add
                            $addCount = $ticket_status_ach['ts_re_val']['tar'] - $ticket_status_ach['ts_re_val']['ach_act'];
                            $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'channel_ach'=>$channel_ach);
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_re_val', $addCount, TRUE, $additionalFlag);
                        } else {
                            // Need to remove
                            $removeCount = $ticket_status_ach['ts_re_val']['ach_act'] - $ticket_status_ach['ts_re_val']['tar'];
                            $additionalFlag = NULL;
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_re_val', $removeCount, FALSE, $additionalFlag);
                        }
                    }
                    
                    $ticket_status_ach = $this->getStatusOnly($modifiedDataset, $ticket_status, $targetDelta = 1);
                    $ticket_status_ach = $ticket_status_ach['ticket_status'];

                    // Conditions for 'ts_fl_val'
                    if ($ticket_status_ach['ts_fl_val']['tar'] > 0 && (abs($ticket_status_ach['ts_fl_val']['tar'] - $ticket_status_ach['ts_fl_val']['ach_act']) / $ticket_status_ach['ts_fl_val']['tar']) >= $delta_per) {
                        if ($ticket_status_ach['ts_fl_val']['tar'] > $ticket_status_ach['ts_fl_val']['ach_act']) {
                            // Need to add
                            $addCount = $ticket_status_ach['ts_fl_val']['tar'] - $ticket_status_ach['ts_fl_val']['ach_act'];
                            $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'channel_ach'=>$channel_ach);
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_fl_val', $addCount, TRUE, $additionalFlag);
                        } else {
                            // Need to remove
                            $removeCount = $ticket_status_ach['ts_fl_val']['ach_act'] - $ticket_status_ach['ts_fl_val']['tar'];
                            $additionalFlag = NULL;
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_fl_val', $removeCount, FALSE, $additionalFlag);
                        }
                    }

                    $ticket_status_ach = $this->getStatusOnly($modifiedDataset, $ticket_status, $targetDelta = 1);
                    $ticket_status_ach = $ticket_status_ach['ticket_status'];
                    
                    if($ticket_status_ach['ts_cl_val']['tar'] > 0 && (abs($ticket_status_ach['ts_cl_val']['tar']-$ticket_status_ach['ts_cl_val']['ach_act'])/$ticket_status_ach['ts_cl_val']['tar']) >= $delta_per)
                    {
                        if($ticket_status_ach['ts_cl_val']['tar'] > $ticket_status_ach['ts_cl_val']['ach_act'])
                        { 
                            //Need to add
                            $addCount = $ticket_status_ach['ts_cl_val']['tar'] - $ticket_status_ach['ts_cl_val']['ach_act'];
                            $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'channel_ach'=>$channel_ach);
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_cl_val', $addCount, TRUE, $additionalFlag);

                        }else{
                            //Need to remove
                            $removeCount = $ticket_status_ach['ts_cl_val']['ach_act'] - $ticket_status_ach['ts_cl_val']['tar'] ;
                            $additionalFlag = NULL;
                            $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'STATUS|ts_cl_val', $removeCount, FALSE, $additionalFlag);
                        }
                    }

                    $ticket_status_ach = $this->getStatusOnly($modifiedDataset, $ticket_status, $targetDelta = 1);
                    $ticket_status_ach = $ticket_status_ach['ticket_status'];
                    $this->printR($ticket_status_ach, 0);

            //CHANNEL
            $channel_ach = $this->getChannelOnly($modifiedDataset, $channel, $targetDelta = 1);
            $channel_ach = $channel_ach['channel'];

            if($channel_ach['ch_inb_val']['tar'] > 0 && (abs($channel_ach['ch_inb_val']['tar']-$channel_ach['ch_inb_val']['ach_act'])/$channel_ach['ch_inb_val']['tar']) >= $delta_per)
            {
                if($channel_ach['ch_inb_val']['tar'] > $channel_ach['ch_inb_val']['ach_act'])
                { 
                    //Need to add
                    $addCount = $channel_ach['ch_inb_val']['tar'] - $channel_ach['ch_inb_val']['ach_act'];
                    $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'ticket_status_ach'=>$ticket_status_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_inb_val', $addCount, TRUE, $additionalFlag);

                }else{
                    //Need to remove
                    $removeCount = $channel_ach['ch_inb_val']['ach_act'] - $channel_ach['ch_inb_val']['tar'] ;
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_inb_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $channel_ach = $this->getChannelOnly($modifiedDataset, $channel, $targetDelta = 1);
            $channel_ach = $channel_ach['channel'];

            // Conditions for 'ch_app_val'
            if ($channel_ach['ch_app_val']['tar'] > 0 && (abs($channel_ach['ch_app_val']['tar'] - $channel_ach['ch_app_val']['ach_act']) / $channel_ach['ch_app_val']['tar']) >= $delta_per) {
                if ($channel_ach['ch_app_val']['tar'] > $channel_ach['ch_app_val']['ach_act']) {
                    // Need to add
                    $addCount = $channel_ach['ch_app_val']['tar'] - $channel_ach['ch_app_val']['ach_act'];
                    $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'ticket_status_ach'=>$ticket_status_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_app_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $channel_ach['ch_app_val']['ach_act'] - $channel_ach['ch_app_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_app_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $channel_ach = $this->getChannelOnly($modifiedDataset, $channel, $targetDelta = 1);
            $channel_ach = $channel_ach['channel'];

            // Conditions for 'ch_ivr_val'
            if ($channel_ach['ch_ivr_val']['tar'] > 0 && (abs($channel_ach['ch_ivr_val']['tar'] - $channel_ach['ch_ivr_val']['ach_act']) / $channel_ach['ch_ivr_val']['tar']) >= $delta_per) {
                if ($channel_ach['ch_ivr_val']['tar'] > $channel_ach['ch_ivr_val']['ach_act']) {
                    // Need to add
                    $addCount = $channel_ach['ch_ivr_val']['tar'] - $channel_ach['ch_ivr_val']['ach_act'];
                    $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'ticket_status_ach'=>$ticket_status_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_ivr_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $channel_ach['ch_ivr_val']['ach_act'] - $channel_ach['ch_ivr_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_ivr_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $channel_ach = $this->getChannelOnly($modifiedDataset, $channel, $targetDelta = 1);
            $channel_ach = $channel_ach['channel'];

            // Conditions for 'ch_ussd_val'
            if ($channel_ach['ch_ussd_val']['tar'] > 0 && (abs($channel_ach['ch_ussd_val']['tar'] - $channel_ach['ch_ussd_val']['ach_act']) / $channel_ach['ch_ussd_val']['tar']) >= $delta_per) {
                if ($channel_ach['ch_ussd_val']['tar'] > $channel_ach['ch_ussd_val']['ach_act']) {
                    // Need to add
                    $addCount = $channel_ach['ch_ussd_val']['tar'] - $channel_ach['ch_ussd_val']['ach_act'];
                    $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'ticket_status_ach'=>$ticket_status_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_ussd_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $channel_ach['ch_ussd_val']['ach_act'] - $channel_ach['ch_ussd_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_ussd_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $channel_ach = $this->getChannelOnly($modifiedDataset, $channel, $targetDelta = 1);
            $channel_ach = $channel_ach['channel'];

            // Conditions for 'ch_mono_val'
            if ($channel_ach['ch_mono_val']['tar'] > 0 && (abs($channel_ach['ch_mono_val']['tar'] - $channel_ach['ch_mono_val']['ach_act']) / $channel_ach['ch_mono_val']['tar']) >= $delta_per) {
                if ($channel_ach['ch_mono_val']['tar'] > $channel_ach['ch_mono_val']['ach_act']) {
                    // Need to add
                    $addCount = $channel_ach['ch_mono_val']['tar'] - $channel_ach['ch_mono_val']['ach_act'];
                    $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'ticket_status_ach'=>$ticket_status_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_mono_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $channel_ach['ch_mono_val']['ach_act'] - $channel_ach['ch_mono_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_mono_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $channel_ach = $this->getChannelOnly($modifiedDataset, $channel, $targetDelta = 1);
            $channel_ach = $channel_ach['channel'];

            
            // Conditions for 'ch_oth_val' (an array of values)
            if ($channel_ach['ch_oth_val']['tar'] > 0 && (abs($channel_ach['ch_oth_val']['tar'] - $channel_ach['ch_oth_val']['ach_act']) / $channel_ach['ch_oth_val']['tar']) >= $delta_per) {
                if ($channel_ach['ch_oth_val']['tar'] > $channel_ach['ch_oth_val']['ach_act']) {
                    // Need to add
                    $addCount = $channel_ach['ch_oth_val']['tar'] - $channel_ach['ch_oth_val']['ach_act'];
                    $additionalFlag = array('ticket_category_ach'=>$ticket_category_ach, 'ticket_status_ach'=>$ticket_status_ach);
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_oth_val', $addCount, TRUE, $additionalFlag);
                } else {
                    // Need to remove
                    $removeCount = $channel_ach['ch_oth_val']['ach_act'] - $channel_ach['ch_oth_val']['tar'];
                    $additionalFlag = NULL;
                    $modifiedDataset = $this->AddRemoveFromDataset($modifiedDataset, $fullDataset, $whatToRemove = 'SOURCE|ch_oth_val', $removeCount, FALSE, $additionalFlag);
                }
            }

            $channel_ach = $this->getChannelOnly($modifiedDataset, $channel, $targetDelta = 1);
            $channel_ach = $channel_ach['channel'];
            $this->printR($channel_ach, 0);

            //$dataset = $modifiedDataset;
            $loopCount++;
            echo '<br/>LoopCount:'.$loopCount;
            //echo '<br/>Check modifiedDataset null:'.is_null($modifiedDataset);
            
            //Now get the modified counts
            //Ticket Category
            $ticket_category_ach = $this->getCategoryOnly($modifiedDataset, $ticket_category, $targetDelta = 1); //$ticket_category
            $ticket_category_ach = $ticket_category_ach['ticket_category'];
            echo '<br/>ticket_category_ach: FINAL';
            $this->printR($ticket_category_ach, 0);

            //Ticket Status
            $ticket_status_ach = $this->getStatusOnly($modifiedDataset, $ticket_status, $targetDelta = 1);
            $ticket_status_ach = $ticket_status_ach['ticket_status'];
            echo '<br/>ticket_status_ach: FINAL';
            $this->printR($ticket_status_ach,0);

            //Channel
            $channel_ach = $this->getChannelOnly($modifiedDataset, $channel, $targetDelta = 1);
            $channel_ach = $channel_ach['channel'];
            echo '<br/>channel_ach: FINAL';
            $this->printR($channel_ach, 0);

            if($loopCount == 5)
            {
                break;
            }

            echo "----------------------END loopcount: ".($loopCount)."--------------------------------";
        }
        
        //exit;
        //echo '<br/>final achieve before return';
        //$this->printR($ticket_category_ach, 0);

        echo '<br/>Total LoopCount:'.$loopCount;

        return $modifiedDataset;
    }

    //Add or remove in existing dataset from $fullDataSet
    //If Add, then only new index will come
    public function AddRemoveFromDataset($dataset, $fullDataset, $whatToRemove, $addRemoveCount, $addRemoveFlag, $additionalFlag)
    {
        $dataset = $this->SwapTableIDAndIndex($dataset);
        echo '<br/>Before $dataset:'.count($dataset);
        //echo '<br/>is_array:'.is_array($additionalFlag);
        

        // if(is_array($additionalFlag))
        // {
        //     $this->printR($additionalFlag, 0);
        //     //dd($additionalFlag);
        // }

        //Based on $cat the mapping variable will be changed
        //Get the corresponding Key Mapping variable
        $whatToRemove = explode("|", $whatToRemove);
        $cat = $whatToRemove[0];
        $subCat = $whatToRemove[1];
        $keyMapping = NULL;

        if($cat === 'CATEGORY'){
            $keyMapping = self::$CategorykeyMappings;
            $keyMapping_STATUS = self::$StatuskeyMappings;
            $keyMapping_CHANNEL = self::$ChannelkeyMappings;

        }else if($cat === 'STATUS'){
            $keyMapping = self::$StatuskeyMappings;
            $keyMapping_CATEGORY = self::$CategorykeyMappings;
            $keyMapping_CHANNEL = self::$ChannelkeyMappings;

        }else if($cat === 'SOURCE'){
            $keyMapping = self::$ChannelkeyMappings;
            $keyMapping_STATUS = self::$StatuskeyMappings;
            $keyMapping_CATEGORY = self::$CategorykeyMappings;
        }

        if($addRemoveFlag == TRUE) 
        {   //Add elements
            //Add items from $fullDataset to $dataset
            //$availableIndexes = array_diff(array_keys($fullDataset), array_keys($dataset));

            //Taking the categories-CATEGORY,STATUS,CHANNEL & sub category
            // $whatToRemove = explode("|", $whatToRemove);
            // $cat = $whatToRemove[0];
            // $subCat = $whatToRemove[1];
            // print_r($whatToRemove);

            //Need to check below function-
            $availableIndexes = array_diff(

                array_filter(array_keys($fullDataset), function($index) use ($fullDataset, $cat, $subCat, $keyMapping, $additionalFlag) {

                    //$keyMapping is array for $ChannelkeyMappings variable
                    //return isset($fullDataset[$index][$cat]) && $fullDataset[$index][$cat] === $keyMapping[$subCat];

                    if(is_array($keyMapping[$subCat]))
                    {
                        return isset($fullDataset[$index][$cat]) && in_array($fullDataset[$index][$cat], $keyMapping[$subCat]);
                    }else{

                        //return isset($fullDataset[$index][$cat]) && $fullDataset[$index][$cat] === $keyMapping[$subCat];

                        if($cat === 'CATEGORY'){
                            $ticket_status_ach = $additionalFlag['ticket_status_ach'];
                            $channel_ach = $additionalFlag['channel_ach'];
                            
                            // $this->printR($ticket_status_ach, 0);
                            // $this->printR($channel_ach, 0);

                            $required_indexes = array();
                            
                            foreach($ticket_status_ach as $key => $val)
                            {
                                if($val['tar'] > 0 && $val['ach_act'] < $val['tar'])
                                {
                                    $required_indexes[] = $key;
                                }
                            }

                            foreach($channel_ach as $key => $val)
                            {
                                if($val['tar'] > 0 && $val['ach_act'] < $val['tar'])
                                {
                                    $required_indexes[] = $key;
                                }
                            }

                            // echo '<br/>Required Indexes:';
                            // $this->printR($required_indexes, 0);

                            if(isset($fullDataset[$index][$cat]))
                            {
                                if($fullDataset[$index][$cat] === $keyMapping[$subCat])
                                {
                                    //Priority of other categories if exists and achieve is less than target
                                    if(!empty($required_indexes))
                                    {
                                        foreach($required_indexes as $ind)
                                        {
                                            //echo '<br/>For CATEGORY,check $status:'.
                                            $status = (array_key_exists($ind, self::$StatuskeyMappings)) && ($fullDataset[$index]['STATUS'] === self::$StatuskeyMappings[$ind]);
                                            //echo '<br/>For CATEGORY,check $channel:'.
                                            $channel = (array_key_exists($ind, self::$ChannelkeyMappings) && ($fullDataset[$index]['SOURCE'] === self::$ChannelkeyMappings[$ind]));

                                            if($status && $channel)
                                            {
                                                return TRUE;
                                            }else if($status || $channel)
                                            {
                                                return TRUE;
                                            }
                                        }
                                    }

                                    //if enabled then there is deviation, if not then outcome is more accurate
                                    //return TRUE;

                                }else{

                                    return FALSE;
                                }

                            }else{

                                return FALSE;
                            }

                            //Original return statement
                            //return isset($fullDataset[$index][$cat]) && $fullDataset[$index][$cat] === $keyMapping[$subCat];

                        }else if($cat === 'STATUS'){
                            $ticket_category_ach = $additionalFlag['ticket_category_ach'];
                            $channel_ach = $additionalFlag['channel_ach'];
                            
                            // $this->printR($ticket_status_ach, 0);
                            // $this->printR($channel_ach, 0);

                            $required_indexes = array();
                            
                            foreach($ticket_category_ach as $key => $val)
                            {
                                if($val['tar'] > 0 && $val['ach_act'] < $val['tar'])
                                {
                                    $required_indexes[] = $key;
                                }
                            }

                            foreach($channel_ach as $key => $val)
                            {
                                if($val['tar'] > 0 && $val['ach_act'] < $val['tar'])
                                {
                                    $required_indexes[] = $key;
                                }
                            }

                            // echo '<br/>Required Indexes:';
                            // $this->printR($required_indexes, 0);

                            if(isset($fullDataset[$index][$cat]))
                            {
                                if($fullDataset[$index][$cat] === $keyMapping[$subCat])
                                {
                                    //Priority of other categories if exists and achieve is less than target
                                    if(!empty($required_indexes))
                                    {
                                        foreach($required_indexes as $ind)
                                        {
                                            //echo '<br/>For STATUS,check $category:'.
                                            $category = (array_key_exists($ind, self::$CategorykeyMappings)) && ($fullDataset[$index]['CATEGORY'] === self::$CategorykeyMappings[$ind]);
                                            //echo '<br/>For STATUS,check $channel:'.
                                            $channel = (array_key_exists($ind, self::$ChannelkeyMappings) && ($fullDataset[$index]['SOURCE'] === self::$ChannelkeyMappings[$ind]));

                                            if($category && $channel)
                                            {
                                                return TRUE;
                                            }else if($category || $channel)
                                            {
                                                return TRUE;
                                            }
                                        }
                                    }

                                    //return TRUE;

                                }else{

                                    return FALSE;
                                }

                            }else{

                                return FALSE;
                            }
                        }else if($cat === 'SOURCE'){
                            $ticket_category_ach = $additionalFlag['ticket_category_ach'];
                            $ticket_status_ach = $additionalFlag['ticket_status_ach'];
                            
                            // $this->printR($ticket_status_ach, 0);
                            // $this->printR($channel_ach, 0);

                            $required_indexes = array();
                            
                            foreach($ticket_category_ach as $key => $val)
                            {
                                if($val['tar'] > 0 && $val['ach_act'] < $val['tar'])
                                {
                                    $required_indexes[] = $key;
                                }
                            }

                            foreach($ticket_status_ach as $key => $val)
                            {
                                if($val['tar'] > 0 && $val['ach_act'] < $val['tar'])
                                {
                                    $required_indexes[] = $key;
                                }
                            }

                            // echo '<br/>Required Indexes:';
                            // $this->printR($required_indexes, 0);

                            if(isset($fullDataset[$index][$cat]))
                            {
                                if($fullDataset[$index][$cat] === $keyMapping[$subCat])
                                {
                                    //Priority of other categories if exists and achieve is less than target
                                    if(!empty($required_indexes))
                                    {
                                        foreach($required_indexes as $ind)
                                        {
                                            //echo '<br/>For CHANNEL,check $category:'.
                                            $category = (array_key_exists($ind, self::$CategorykeyMappings)) && ($fullDataset[$index]['CATEGORY'] === self::$CategorykeyMappings[$ind]);
                                            //echo '<br/>For CHANNEL,check $status:'.
                                            $status = (array_key_exists($ind, self::$StatuskeyMappings) && ($fullDataset[$index]['STATUS'] === self::$StatuskeyMappings[$ind]));

                                            if($category && $status)
                                            {
                                                return TRUE;
                                            }else if($category || $status)
                                            {
                                                return TRUE;
                                            }
                                        }
                                    }

                                    //return TRUE;

                                }else{

                                    return FALSE;
                                }

                            }else{

                                return FALSE;
                            }
                        }

                    }

                }),array_keys($dataset)

            );

            // echo '$availableIndexes';
            // $this->printR($availableIndexes,0);

            //shuffle($availableIndexes);

            if (!empty($availableIndexes)) {
                // Randomly select indexes from $fullDataset
                echo "<br/>No of items to add for $subCat:".$addRemoveCount;

                $selectedIndexes = array_rand($availableIndexes, min($addRemoveCount, count($availableIndexes)));
    
                if (is_array($selectedIndexes)) {

                    foreach ($selectedIndexes as $index) {
                        $dataset[$availableIndexes[$index]] = $fullDataset[$availableIndexes[$index]];
                    }

                } else {
                    //Might return single element
                    $dataset[$availableIndexes[$selectedIndexes]] = $fullDataset[$availableIndexes[$selectedIndexes]];
                }
            }

            return $dataset;

        }else{ //Remove elements
            
            // $whatToRemove = explode("|", $whatToRemove);
            // $cat = $whatToRemove[0];
            // $subCat = $whatToRemove[1];
            //print_r($whatToRemove);

            //Remove from $datset
            //Take 
            //$this->printR($dataset,0);
            echo "<br/>Delete for $subCat:".$addRemoveCount;
            //$this->printR(self::$CategorykeyMappings,1);

            $removedCount = 0; // Counter for removed elements

            foreach ($dataset as $index => $data) {

                if ($removedCount >= $addRemoveCount) {
                    break; // Exit the loop when the desired number of elements have been removed
                }

                //$this->printR($index,0);
                //$this->printR($data,0);
                //exit
                // echo '$data[$cat]:'.$data[$cat];
                // echo 'self::$CategorykeyMappings[$subCat]:'. self::$CategorykeyMappings[$subCat];

                if (isset($data[$cat]) && $data[$cat] === $keyMapping[$subCat]) {
                    // Remove the element from $dataset
                    unset($dataset[$index]);
                    //echo "<br/>removed $index element:".$removedCount;
                    $removedCount++; // Increment the counter
                }
            }

            //echo 'After $dataset'.count($dataset);
            //$this->printR($dataset,1);
            //exit;
            return $dataset;
        }

        // Reindex the array if needed
        // $dataset = array_values($dataset);

        //}
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
            'nqc_val' => array('tar'=>$nqc_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
	        'oth_val' => array('tar'=>$oth_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
	        'src_val' => array('tar'=>$src_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
	        'prc_val' => array('tar'=>$prc_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
	        'irc_val' => array('tar'=>$irc_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),

            'pcc_val' => array('tar'=>$pcc_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'vasc_val' => array('tar'=>$vasc_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'sr_val' => array('tar'=>$sr_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'rcc_val' => array('tar'=>$rcc_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'pbc_val' => array('tar'=>$pbc_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'toffee_val' => array('tar'=>$toffee_val,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'csc_val' => array('tar'=>$csc_val,'ach'=>0, 'ach_act'=>0, 'max'=>0)
        );
    }

    public function makeTicketStatus($request)
    {
        return $ticket_status = array(
            'ts_ass_val' => array('tar'=>$request->{'ts-ass-val'} ,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ts_tf_val' => array('tar'=>$request->{'ts-tf-val'} ,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ts_re_val' => array('tar'=>$request->{'ts-re-val'} ,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ts_fl_val' => array('tar'=>$request->{'ts-fl-val'} ,'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ts_cl_val' => array('tar'=>$request->{'ts-cl-val'} ,'ach'=>0, 'ach_act'=>0, 'max'=>0)
        );
    }
    
    public function makeChannel($request)
    {
        return $channel = array(
            'ch_inb_val' => array('tar'=>$request->{'ch-inb-val'}, 'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ch_app_val' => array('tar'=>$request->{'ch-app-val'}, 'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ch_ivr_val' => array('tar'=>$request->{'ch-ivr-val'}, 'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ch_ussd_val' => array('tar'=>$request->{'ch-ussd-val'}, 'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ch_mono_val' => array('tar'=>$request->{'ch-mono-val'}, 'ach'=>0, 'ach_act'=>0, 'max'=>0),
            'ch_oth_val' => array('tar'=>$request->{'ch-oth-val'}, 'ach'=>0, 'ach_act'=>0, 'max'=>0)
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

    public function getSecondLayerFlag($request)
    {
        $category_flag_zero = 0;
        $status_flag_zero = 0;
        $channel_flag_zero = 0;

        if(isset($request->{'set-Ticket-Category-zero'}))
            $category_flag_zero = 1;

        if(isset($request->{'set-Ticket-Status-zero'}))
            $status_flag_zero = 1;
        
        if(isset($request->{'set-Ticket-Channel-zero'}))
            $channel_flag_zero = 1;
        

        return $flagArray = array(
                'category_flag_zero' => $category_flag_zero,
                'status_flag_zero' => $status_flag_zero,
                'channel_flag_zero' => $channel_flag_zero
            );
        
    }

    public function SwapTableIDAndIndex($array)
    {
        $newArray = array();

        foreach ($array as $key => $value) {
            //$array[$key]["tableID"] = $value["tableID"];
            $newArray[$value["tableID"]] = $array[$key];
        }

        //dd($newArray);

        return $newArray;
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
