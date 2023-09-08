<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SamplingModelOutbound extends Model
{
    use HasFactory;

    //protected $table = 'data_ib_so_soins';

    public static function getAllParamenter()
    {
        $query = "select c.Id, b.Id, a.Id as samplingID, a.criteriaTypeID, b.criteriaType, a.samplingCriteria, a.sampling_value_in_percent from sampling_criteria a, criteria_type b, category c 
        where a.criteriaTypeID=b.Id and b.categoryID=c.Id and c.category='OUTBOUND'";

        $results = DB::select($query);

        return $results;
    }

    public static function getAuditPerAgent($tableID = null)
    {
        $userType = session()->get('userType');

        if(isset($tableID))
        {
            $query = "select * from ob_fixed_agent where type = '$userType' and tableID = '$tableID' limit 0,1";
        }else{
            $query = "select * from ob_fixed_agent where type = '$userType' limit 0,1";
        }

        $results = DB::select($query);

        return $results;
    }

    public static function bookingStatusCheck($type)
    {
        $results = DB::table('booking')
                        ->select()
                        ->where('type',$type)
                        ->limit(1)
                        ->get()
                        ->toArray();
        return $results;
    }

    public static function bookingUpdate($user, $type, $from, $to)
    {
        $inputs = [
            'booking_by' => $user,
            'type' => $type,
            'created_at' => now()
        ];

        DB::table('booking')->insert($inputs);



        $booking_history = [
            'booking_by' => $user,
            'from' => date('Y-m-d', strtotime($from)),
            'to' => date('Y-m-d', strtotime($to)),
            'created_at' => now()
        ];

        DB::table('booking_history')->insert($booking_history);
    }

    public static function getDataSetFromTable($from, $to)
    {
        //First truncate temporary table
        DB::table('tmp_data_ib_so_casedetailednew')->truncate();

        //Insert into tmp table
        $sql_insert_tmp = "
            insert into tmp_data_ib_so_casedetailednew (parentID,pickupFlag,reportdate,ID,MSISDN,STATUS,SOURCE,CATEGORY,`SUBCATEGORY`,`OPENDATE`,`ALTERNATECONTACT`)
            select a.tableID, a.pickupFlag, a.reportdate, a.ID, a.MSISDN, a.STATUS, a.SOURCE, a.CATEGORY, a.`SUB CATEGORY`,a.`OPEN DATE`, a.ALTERNATECONTACT
            from data_ib_so_casedetailednew a
            where a.reportdate between STR_TO_DATE('$from','%m/%d/%Y') AND STR_TO_DATE('$to','%m/%d/%Y')
            and a.MSISDN <> 0
            and a.pickupFlag = 0
            and a.STATUS <> 'Not Reached'";
            //excluding not reached
         
        $results = DB::select($sql_insert_tmp);

        //Now fetch from tmp table
        $selection_query =  "select a.parentID, a.ID as SOTicket, a.MSISDN, a.STATUS, a.SOURCE, a.CATEGORY
        from tmp_data_ib_so_casedetailednew a order by RAND()"; //order by random

        $results = DB::select($selection_query);

        //dd($results);

        return $results;
    }

    /*
    * Function not in use
    *
    *
    */
    public static function getAuditCountByAgents_old($userType)
    {
        $query = "
        select ag.agentid, count(*) cnt from 
        (
            select agentid from `qaoutbound-uat`.calleval_new
            where `date` BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()-1
            and userid in (select distinct(userid) from `qaoutbound-uat`.users where AgentType='$userType')
        ) as ag
        group by ag.agentid
        order by cnt desc"; //CURDATE()-1

        $results = DB::select($query);

        return $results;
    }


    public static function getSelectedSample($finalArray)
    {
        //update the pickup flag
        $update = DB::table('tmp_data_ib_so_casedetailednew')
                        ->whereIn('parentID',$finalArray)
                        ->update(['pickupFlag' => '1']);

        //select data which has parentIDs
        $results = DB::table('tmp_data_ib_so_casedetailednew')->whereIn('parentID',$finalArray)->get();

        return $results;
    }

    public static function GetUserList($user)
    {
        //UAT database
        $sql = "select distinct(userid) from `qaoutbound-uat`.`users`
		where user_type='1'
		and AgentType='$user'";      

        $results = DB::select($sql);
            
        return $results;
    }

    public static function GetCallIDs()
    {
        $results = DB::table('tmp_data_ib_so_casedetailednew')
                        ->select('parentID','reportdate','ID','MSISDN','STATUS','SOURCE','CATEGORY','SUBCATEGORY','OPENDATE','ALTERNATECONTACT')
                        ->where('pickupFlag','1')
                        ->get()
                        ->toArray();

        return $results;
    }

    public static function GetUserListByUserID($userArray)
    {
        $userArray = array_keys($userArray);

        $str = implode("','", $userArray);
        $str = "'".$str."'";
        //dd($str);

        $sql = "select distinct(userid) from `qaoutbound-uat`.`users`
		where userid in ($str)";

        $users = DB::select($sql);

        return $users;
        
    }

    public static function assignCallsToUsers($user_with_CALLS_array)
    {
        //dd($user_with_CALLS_array);
        $assignedBy = session()->get('email');

        $insertData = [];
        //Format the data to insert in single query
        foreach ($user_with_CALLS_array as $assignedTo => $values) {
            foreach ($values as $value) {
                $insertData[] = [
                    'assignedTo' => $assignedTo,
                    'assignedBy' => $assignedBy,
                    'parentID' => $value->parentID,
                    'REPORTDATE' => $value->reportdate,
                    'SOID' => $value->ID,
                    'MSISDN' => $value->MSISDN,
                    'STATUS' => $value->STATUS,
                    'SOURCE' => $value->SOURCE,
                    'CATEGORY' => $value->CATEGORY,
                    'SUBCATEGORY' => $value->SUBCATEGORY,
                    'OPENDATE' => $value->OPENDATE,
                    'ALTERNATECONTACT' => $value->ALTERNATECONTACT
                ];
            }
        }

        //printR($insertData ,1);

        //dd($insertData);

        $res = DB::table('assigned_ob')->insert($insertData);

        //Now truncate temporary table, truncate is done when selecting dataset
        //DB::table('tmp_data_ib_so_soins')->truncate();

        return $insertData;
    }


    public static function UpdateSourceTable($insertedCallsWithUsers)
    {
                
        //Only those call's pickupFlag can be set 1 if you take the assigned array
        //by updating, firstly set tmp_data_ib_so_soins's pickupFlag to 2 and then update source table data_ib_so_soins with 1
        //by executing below query
        

        //dd($user_with_calls);

        $callsWithParentIDs = self::getCallsWithParentId($insertedCallsWithUsers);
        
        //update the pickupFlag set to 2
        $update = DB::table('tmp_data_ib_so_casedetailednew')
                  ->whereIn('parentID',$callsWithParentIDs)
                  ->update(['pickupFlag' => '2']);


        //dd($update);

        //Now update original table with status 1
        $query = "UPDATE data_ib_so_casedetailednew a
                INNER JOIN tmp_data_ib_so_casedetailednew b ON a.tableID = b.parentID 
                AND a.ID = b.ID 
                SET a.pickupFlag = 1 
                WHERE b.pickupFlag = 2";

        return $results = DB::update($query);
    }

    public static function getCallsWithParentId($insertedCallsWithUsers)
    {    
        $parentID = [];

        foreach($insertedCallsWithUsers as $u => $values)
        {   
            $parentID[] = $values['parentID'];
        }

        //dd($parentID);
        
        return $parentID;
    }

    public static function DeleteBookingTable($type)
    {
        $delete = DB::table('booking')
                      ->where('type', $type)
                      ->delete();

        return $delete;
    }

    public static function GetAssignedCallsFromDB($userid)
    {       
        /*
        $results = DB::table('assigned_ob')
                        ->select('SOID','MSISDN','STATUS','SUBCATEGORY','ALTERNATECONTACT') //'OPENDATE'
                        ->where('assignedTo',$userid)
                        ->where('auditStatus',0)
                        ->orderBy('assignedDate','desc') //FIFO applied
                        //->inRandomOrder()
                        ->limit(1)
                        ->get()
                        ->toArray();
        */

        //echo $results->toSql();
        //dd();

        // $StatusWiseTableColumnMapping = array(
        //     'Assigned' => 'ASSIGNEDDATE',
        //     'Follow Up' => 'FOLLOWUPDATE',
        //     'Closed' => 'CLOSEDDATE',
        //     'Re Assigned' => 'REASSIGNEDDATE',
        //     'Technical Feedback' => 'TECHNICALFEEDBACKDATE'
        //     //'assigned_ob.STATUS' => '_BLANK_'
        // );

        $results = DB::table('assigned_ob')
        ->select(
                'assigned_ob.SOID',
                'assigned_ob.MSISDN',
                'assigned_ob.STATUS',
                'assigned_ob.SUBCATEGORY',
                'assigned_ob.ALTERNATECONTACT'
        )
        //->select(DB::raw("data_ib_so_casedetailednew.{$StatusWiseTableColumnMapping['assigned_ob.STATUS']} as dynamic_column"))
        //->select(DB::raw("data_ib_so_casedetailednew." .$StatusWiseTableColumnMapping[DB::raw("assigned_ob.STATUS")] ." as dynamic_column"))
        
        //Applying the logic in raw query
        ->selectRaw('CASE 
                    WHEN assigned_ob.STATUS = "Assigned" THEN data_ib_so_casedetailednew.ASSIGNEDDATE
                    WHEN assigned_ob.STATUS = "Follow Up" THEN data_ib_so_casedetailednew.FOLLOWUPDATE
                    WHEN assigned_ob.STATUS = "Closed" THEN data_ib_so_casedetailednew.CLOSEDDATE
                    WHEN assigned_ob.STATUS = "Re Assigned" THEN data_ib_so_casedetailednew.REASSIGNEDDATE
                    WHEN assigned_ob.STATUS = "Technical Feedback" THEN data_ib_so_casedetailednew.TECHNICALFEEDBACKDATE
                    ELSE NULL
                 END as STATUS_DATE')

        ->leftjoin('data_ib_so_casedetailednew', 'assigned_ob.parentID', '=', 'data_ib_so_casedetailednew.tableID')
        ->where('assigned_ob.assignedTo',$userid)
        ->where('assigned_ob.auditStatus',0)
        ->orderBy('assigned_ob.assignedDate','desc') //FIFO applied
        //->inRandomOrder()
        ->limit(1)
        ->get()
        //->toSql();
        ->toArray();
        //dd($results);

        return $results;

    //     $results = DB::table('assigned_ob')
    // ->select('assigned_ob.SOID', 'assigned_ob.MSISDN', 'assigned_ob.STATUS', 'assigned_ob.SUBCATEGORY', 'assigned_ob.ALTERNATECONTACT')
    // ->select(DB::raw('data_ib_so_casedetailednew.' . $StatusWiseTableColumnMapping['assigned_ob.STATUS'] . ' as dynamic_column'))
    // ->leftJoin('data_ib_so_casedetailednew', 'assigned_ob.parentID', '=', 'data_ib_so_casedetailednew.tableID')
    // ->where('assignedTo', $userid)
    // ->where('auditStatus', 0)
    // ->orderBy('assignedDate', 'desc')
    // ->limit(1)
    // ->get()
    // ->toArray();
    }

    public function GetTableColumn($index)
    {
        $StatusWiseTableColumnMapping = array(
            'Assigned' => 'ASSIGNEDDATE',
            'Follow Up' => 'FOLLOWUPDATE',
            'Closed' => 'CLOSEDDATE',
            'Re Assigned' => 'REASSIGNEDDATE',
            'Technical Feedback' => 'TECHNICALFEEDBACKDATE'
            //'assigned_ob.STATUS' => '_BLANK_'
        );

        return $StatusWiseTableColumnMapping[$index];
    }

    public static function UpdateIgnoredCallsInDB($callid, $userid, $reason)
    {
           $auditStatus = 2; //Audit status=2 means call is ignored

           $rowsAffected = DB::table('assigned_ob')
                         ->where('assignedTo',$userid)
                         ->where('SOID',$callid)
                         ->update(['ignoredComment' => $reason,
                                    'auditStatus' => $auditStatus]);

           return $rowsAffected;
    }

    public static function UpdateAuditPerAgentByID($tableID, $newVal)
    {
        $upDatedBy = session()->get('email');

        $rowsAffected = DB::table('ob_fixed_agent')
                            ->where('tableID',$tableID)
                            ->update(['value' => $newVal,
                                      'updatedBy' => $upDatedBy,
                                      'updatedDate' => now()
                                    ]);
        return $rowsAffected;
    }

    public static function getUserTypeAndAuditPerAgent($userid)
    {
        // $results = DB::table('users')
        //                 ->select('userType')
        //                 ->whereRaw("SUBSTRING_INDEX(email, '@', 1) = ?", [$userid])
        //                 ->limit(1)
        //                 ->get()
        //                 ->toArray();
        $query = "
                    select a.userType, b.value from
                    users a, ob_fixed_agent b
                    where SUBSTRING_INDEX(a.email,'@',1) = '$userid'
                    and b.type = a.userType
                    limit 0,1
                ";
                
        $results = DB::select($query);

        return $results;
                    
    }

    public static function getAuditCountByAgents($agentid, $userType)
    {
        $query = "
       
            select count(*) totalAuditedCalls from `qaoutbound-uat`.calleval_new
            where `date` BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()-1
            and agentid = '$agentid'
            and userid in (select distinct(userid) from `qaoutbound-uat`.users where AgentType='$userType')
        
       "; //CURDATE()-1

        $results = DB::select($query);

        return $results;
    }


    public static function insertSamplingHistory($user, $target, $result)
    {  
        if($target>$result)
        {
            $change_in_percent = '-'.(($target-$result)/$target)*100;
        }
        else{
            $change_in_percent = '+'.(($result-$target)/$target)*100;
        }
        
        $inputs = [
            'type'=>'OB',
            'generated_by'=> $user,
            'target_audit'=> $target,
            'actual_outcome'=> $result,
            'change_in_percent'=> $change_in_percent,
            'created_at'=>now()
        ];

        //Return the last insert ID
        return DB::table('sampling_generation_history')->insertGetId($inputs);

    }
}
