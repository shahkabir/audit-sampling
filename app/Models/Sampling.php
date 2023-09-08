<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sampling extends Model
{
    use HasFactory;

    protected $table = 'data_ib_so_soins';


    public static function getAllParamenter()
    {
        $query = "  SELECT
                        `c`.`Id`,
                        `b`.`Id`,
                        `a`.`Id` AS 'samplingID',
                        `a`.`criteriaTypeID`,
                        `b`.`criteriaType`,
                         REPLACE(REPLACE(`a`.`samplingCriteria`, 'X', `d`.`min`), 'Y', `d`.`max`) AS 'samplingCriteria',
                        `a`.`sampling_value_in_percent`,
                        `d`.`min`,
                        `d`.`max`
                    FROM
                        `criteria_type` b,
                        `category` c,
                        `sampling_criteria` a left join sampling_params d on a.Id=d.samplingCriteriaID
                    WHERE
                        `a`.`criteriaTypeID` = `b`.`Id` AND `b`.`categoryID` = `c`.`Id` 
                    AND `c`.`category` = 'INBOUND'
                    ORDER by samplingID asc";

        $results = DB::select($query);

        return $results;
    }

    public static function GetParameterByID($id = null)
    {
        if(isset($id))
        {
            $query = "select * from sampling_params a where a.samplingCriteriaID=$id";
        }else{
            $query = "select * from sampling_params";
        }

        $results = DB::select($query);
        return $results;
    }
    
    public static function UpdateParameterByID($samplingCriteriaID, $maxNew, $minNew)
    {
        $rowsAffected = DB::table('sampling_params')
                            ->where('samplingCriteriaID',$samplingCriteriaID)
                            ->update(['min' => $minNew,
                                      'max' => $maxNew
                                    ]);

        return $rowsAffected;
    }

        
    public static function getDurationRange()
    {
        $query = "SELECT * from sampling_params";
        $results = DB::select($query);

        return $results;
    }

    public static function getAllWC($wc = null)
    {
        if(isset($wc))
        {
            $query = "select * from wc where cwc='$wc'";
        }else{
            $query = "  select a.*,b.count_till_date,b.audit_target_in_per from wc as a 
                        left join wc_summary as b on a.cwc=b.wc
                        where b.date_of_summary=curdate()-1"; //should be curdate()-1
        }
        $results = DB::select($query);

        $query_sum = "select sum(target_per) as total from wc";
        $sum = DB::select($query_sum);

        return array('results' => $results,
                      'total' => $sum
                    );
    }

    public static function UpdateWC($wc, $newValue)
    {
        $rowsAffected = DB::table('wc')
                         ->where('cwc',$wc)
                         ->update(['target_per' => $newValue]);

        return $rowsAffected;
    }

    public static function SaveAll($wcs)
    {
        foreach($wcs as $wc => $value)
        {
            DB::table('wc')
                ->where('cwc',$wc)
                ->update(['target_per' => $value]);
        }

        return true;
    }

    public static function getAllWCData()
    {
        //This query result will not show any data from 00 till 07hrs for a particular date
        $query = "  select wc,`query`,count_till_date,audit_target_in_per,b.target_per
                    from wc_summary a left join wc b on a.wc=b.cwc
                    where date_of_summary = curdate()-1
                    and audit_target_in_per >0.00
                    order by count_till_date desc";//category
                    //need to update to curdate()-1
                    //'2023-07-22'

        $results = DB::select($query);

        return $results;
    }

    public static function getDataSetFromTable($from, $to)
    {

        //First truncate temporary table
        DB::table('tmp_data_ib_so_soins')->truncate();

        //Insert into tmp table
        $sql_insert_tmp = "insert into `tmp_data_ib_so_soins` (parentID,ID,`START TIME`,CALLID,MSISDN,DURATION,CODE,CATEGORY,QUERY,OUTCOME,UCID_CONNECT,SKILLNO,TALKTIME,WRAPUPCODE,ANSLOGIN,`CREATED BY`)
        (select a.tableID, a.ID, a.`START TIME`, a.CALLID,b.MSISDN,a.DURATION,a.CODE,a.CATEGORY,a.QUERY,a.OUTCOME,b.UCID_CONNECT, b.SKILLNO, b.TALKTIME,b.WRAPUPCODE,b.ANSLOGIN,a.`CREATED BY`
		from data_ib_so_soins a, data_ib_avaya_icd b
		where a.CALLID = b.UCID_CONNECT
		and a.`START TIME` between STR_TO_DATE('$from','%m/%d/%Y') and STR_TO_DATE('$to','%m/%d/%Y')
		and a.pickupFlag = 0
        and a.OUTCOME <> 'UPSELL'
        and b.SKILLNO in (11,12,13,14,15,16,17)
        and a.DIRECTION = 'Incoming'
		order by RAND()
		)";
        //Filter using direction as Incoming, added on 03-June-2023
        //removed UPSELL from selection
        //msisdn taken from avaya source
        //added SKILLNO


        $results = DB::select($sql_insert_tmp);

        //Now fetch from tmp table
        $selection_query =  "select a.parentID, a.DURATION,a.CODE,a.OUTCOME,a.SKILLNO,a.`CREATED BY` as AGENTID
                             from tmp_data_ib_so_soins a
                             order by RAND()";//a.DURATION ASC

        // $selection_query = "select a.tableID, a.DURATION,a.CODE,a.OUTCOME,b.SKILLNO
        //                     from data_ib_so_soins a, data_ib_avaya_icd b
        //                     where a.CALLID = b.UCID_CONNECT
        //                     and a.`START TIME` between STR_TO_DATE('$from','%m/%d/%Y') and STR_TO_DATE('$to','%m/%d/%Y')
        //                     and a.pickupFlag=0
        //                     order by rand()";

         $results = DB::select($selection_query);
        
         //dd($results);

         return $results;
    }


    public static function getSelectedSample($finalArray)
    {
        //update the pickup flag
        $update = DB::table('tmp_data_ib_so_soins')
                        ->whereIn('parentID',$finalArray)
                        ->update(['pickupFlag' => '1']);

        //select data which has parentIDs
        $results = DB::table('tmp_data_ib_so_soins')->whereIn('parentID',$finalArray)->get();

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
            'type'=>'IB',
            'generated_by'=> $user,
            'target_audit'=> $target,
            'actual_outcome'=> $result,
            'change_in_percent'=> $change_in_percent,
            'created_at'=>now()
        ];

        //Return the last insert ID
        return DB::table('sampling_generation_history')->insertGetId($inputs);

    }

    public static function updateBookingHistoryStatus($sampleHistoryID)
    {
        $update = DB::table('sampling_generation_history')
                        ->where('id',$sampleHistoryID)
                        ->update(['isAssigned' => '1']);
        
        return $update;
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

    public static function checkSampleGenerationToday($assignedBy)
    {
        $query = "select generated_by, isAssigned, a.created_at from sampling_generation_history a 
        inner join users b on a.generated_by=b.email
        where date(a.created_at)=curdate()
        and b.userType=(select userType from users where email='$assignedBy')
        and a.isAssigned=1
        order by a.created_at desc
        limit 0,1";

        return $results = DB::select($query);

        //dd($results);
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

    public static function truncateBookingTable()
    {
        DB::table('booking')->truncate();
    }

    public static function GetUserList($user)
    {
        //UAT database
        $sql = "select distinct(user_id) from `qain3`.`users`
		where user_type='1'
		and AgentType='$user'";

        $results = DB::select($sql);
            
        return $results;
    }

    public static function GetUserListByUserID($userArray)
    {
        //dd($userArray);
        // $connection = DB::connection('qain3-uat');

        // $users = $connection->table('users')
        //             ->select('distinct(user_id)')
        //             ->whereIn('user_id', $userArray)
        //             ->get();

        $str = implode("','", $userArray);
        $str = "'".$str."'";
        //dd($str);

        $sql = "select distinct(user_id) from `qain3`.`users`
		where user_id in ($str)";

        $users = DB::select($sql);

        return $users;
        
		// ";
    }

    public static function GetCallIDs()
    {
        $results = DB::table('tmp_data_ib_so_soins')
                        ->select('parentID','CALLID','MSISDN','DURATION','CODE','OUTCOME','SKILLNO')
                        //->select('parentID','CALLID')
                        ->where('pickupFlag','1')
                        ->get()
                        ->toArray();

        return $results;
    }

    public static function getUserType($email)
    {
        $results = DB::table('users')
                        ->select('userType','name')
                        ->where('email',$email)
                        ->limit(1)
                        ->get()
                        ->toArray();
        return $results;
    }

    public static function GetAssignedCallsFromDB($userid)
    {
        $results = DB::table('assigned_ib')
                        ->select('CALLID')
                        ->where('assignedTo',$userid)
                        ->where('auditStatus',0)
                        ->orderBy('assignedDate','desc') //FIFO applied
                        //->inRandomOrder()
                        ->limit(1)
                        ->get()
                        ->toArray();

        //$results->toSql();
        //exit;

        return $results;

    }

    public static function UpdateIgnoredCallsInDB($callid, $userid, $reason)
    {
           $auditStatus = 2; //Audit status=2 means call is ignored

           $rowsAffected = DB::table('assigned_ib')
                         ->where('assignedTo',$userid)
                         ->where('CALLID',$callid)
                         ->update(['ignoredComment' => $reason,
                                    'auditStatus' => $auditStatus]);

           return $rowsAffected;
    }

    public static function UpdateAuditStatusInDB($callid, $userid)
    {
        $auditStatus = 1; //Audit status=2 means call is ignored

        $rowsAffected = DB::table('assigned_ib')
                         ->where('assignedTo',$userid)
                         ->where('CALLID',$callid)
                         ->update(['auditStatus' => $auditStatus,
                                    'auditDate' => now()]);

        return $rowsAffected;
    }

    public static function assignCallsToUsers($user_with_calls)
    {
        //dd($user_with_calls);
        $assignedBy = session()->get('email');

        $insertData = [];
        //Format the data to insert in single query
        foreach ($user_with_calls as $assignedTo => $values) {
            foreach ($values as $value) {
                $insertData[] = [
                    'assignedTo' => $assignedTo,
                    'assignedBy' => $assignedBy,
                    'parentID' => $value->parentID,
                    'CALLID' => $value->CALLID,
                    'DURATION' => $value->DURATION,
                    'SKILLNO' => $value->SKILLNO,
                    'CODE' => $value->CODE,
                    'OUTCOME' => $value->OUTCOME,
                    'MSISDN' => $value->MSISDN
                ];
            }
        }

        //printR($insertData ,1);

        //dd($insertData);

        $res = DB::table('assigned_ib')->insert($insertData);

        //Now truncate temporary table, truncate is done when selecting dataset
        //DB::table('tmp_data_ib_so_soins')->truncate();

        return $insertData;

    }

    public static function UpdateSourceTable($user_with_calls)
    {
        // return static::join('tmp_data_ib_so_soins as b', function ($join) {
        //                 $join->on('data_ib_so_soins.tableID', '=', 'b.parentID')
        //                 ->on('data_ib_so_soins.CALLID', '=', 'b.CALLID');
        // })
        // ->where('b.pickupFlag', '=', 1)
        // ->update(['data_ib_so_soins.pickupFlag' => 1]);

        //Raw query-1
        // update data_ib_so_soins a, tmp_data_ib_so_soins b set a.pickupFlag=1
        // where a.tableID=b.parentID
        // and a.CALLID=b.CALLID
        // and b.pickupFlag=1

        //Raw query-2
        // UPDATE data_ib_so_soins a
        // INNER JOIN tmp_data_ib_so_soins b ON a.tableID = b.parentID 
        // AND a.CALLID = b.CALLID 
        // SET a.pickupFlag = 1 
        // WHERE b.pickupFlag = 1;
        
        //Only those call's pickupFlag can be set 1 if you take the assigned array
        //by updating, firstly set tmp_data_ib_so_soins's pickupFlag to 2 and then update source table data_ib_so_soins with 1
        //by executing below query
        

        //dd($user_with_calls);

        $callsWithParentIDs = self::getCallsWithParentId($user_with_calls);
        
        //update the pickupFlag set to 2
        $update = DB::table('tmp_data_ib_so_soins')
                  ->whereIn('parentID',$callsWithParentIDs)
                  ->update(['pickupFlag' => '2']);


        //dd($update);

        //Now update original table with status 1
        $query = "UPDATE data_ib_so_soins a
                INNER JOIN tmp_data_ib_so_soins b ON a.tableID = b.parentID 
                AND a.CALLID = b.CALLID 
                SET a.pickupFlag = 1 
                WHERE b.pickupFlag = 2";

        return $results = DB::update($query);
    }

    public static function getCallsWithParentId($user_with_calls)
    {    
        $parentID = [];

        foreach($user_with_calls as $u => $values)
        {   
            $parentID[] = $values['parentID'];
        }

        //dd($parentID);
        
        return $parentID;
    }

    public static function checkAuditStatus($type)
    {
        $callProcedure = DB::statement("CALL TRUNCATE_PROC('$type')");
    }

    public static function getAuditCountByAgents($userType)
    {
        $query = "
        select ag.agentid, count(*) cnt from 
        (
            select agentid from `qain3`.calleval_qa
            where `date` BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()-1
            and user_id in (select distinct(user_id) from `qain3`.users where AgentType='$userType')
            union all
            select agentid from `qain3`.calleval_tl
            where `date` BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()-1
            and user_id in (select distinct(user_id) from `qain3`.users where AgentType='$userType')
        ) as ag
        group by ag.agentid
        order by cnt desc"; //CURDATE()-1

        $results = DB::select($query);

        return $results;
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
