<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sampling extends Model
{
    use HasFactory;


    public static function getAllParamenter()
    {
        $query = "select c.Id, b.Id, a.Id as samplingID, a.criteriaTypeID, b.criteriaType, a.samplingCriteria, a.sampling_value_in_percent from sampling_criteria a, criteria_type b, category c where a.criteriaTypeID=b.Id and b.categoryID=c.Id and c.category='INBOUND'";

        $results = DB::select($query);

        return $results;
    }

    public static function getAllWCData()
    {
        //This query result will not show any data from 00 till 07hrs for a particular date
        $query = "  select wc,`query`,count_till_date,audit_target_in_per
                    from wc_summary 
                    where date_of_summary=curdate()-1
                    and audit_target_in_per >0.00
                    order by count_till_date desc";//category
                    //curdate()-2

        $results = DB::select($query);

        return $results;
    }

    public static function getDataSetFromTable($from, $to)
    {

        //Insert into tmp table
        $sql_insert_tmp = "insert into `tmp_data_ib_so_soins` (parentID,ID,`START TIME`,CALLID,MSISDN,DURATION,CODE,CATEGORY,QUERY,OUTCOME,UCID_CONNECT,SKILLNO,TALKTIME,WRAPUPCODE,ANSLOGIN,AGENTNAME)
        (select a.tableID, a.ID, a.`START TIME`, a.CALLID,a.MSISDN,a.DURATION,a.CODE,a.CATEGORY,a.QUERY,a.OUTCOME,b.UCID_CONNECT, b.SKILLNO, b.TALKTIME,b.WRAPUPCODE,b.ANSLOGIN,b.AGENTNAME
		from data_ib_so_soins a, data_ib_avaya_icd b
		where a.CALLID = b.UCID_CONNECT
		and a.`START TIME` between STR_TO_DATE('$from','%m/%d/%Y') and STR_TO_DATE('$to','%m/%d/%Y')
		and a.pickupFlag=0
		order by rand()
		)";

        $results = DB::select($sql_insert_tmp);

        //Now fetch from tmp table
        $selection_query =  "select a.parentID, a.DURATION,a.CODE,a.OUTCOME,a.SKILLNO
                             from tmp_data_ib_so_soins a";

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

        $results = DB::table('tmp_data_ib_so_soins')->whereIn('parentID',$finalArray)->get();

        return $results;
    }

    public static function insertSamplingHistory($target,$result)
    {  
        if($target>$result)
        {
            $change_in_percent = '-'.(($target-$result)/$target)*100;
        }
        else{
            $change_in_percent = '+'.(($result-$target)/$target)*100;
        }
        
        $inputs = [
            'target_audit'=>$target,
            'actual_outcome'=>$result,
            'change_in_percent'=>$change_in_percent,
            'created_at'=>now()
        ];

        DB::table('sampling_generation_history')->insert($inputs);

    }

    public static function bookingUpdate($user, $from, $to)
    {
        $inputs = [
            'booking_by' => $user,
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

    public static function GetUserList($user)
    {
        //UAT database
        $sql = "select distinct(user_id) from `qain3-uat`.`users`
		where user_type='1'
		and AgentType='$user'";

        $results = DB::select($sql);
        
        return $results;
    }

    public static function GetCallIDs()
    {
        $results = DB::table('tmp_data_ib_so_soins')
                        ->select('parentID','CALLID')
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
                        ->inRandomOrder()
                        ->limit(1)
                        ->get()
                        ->toArray();

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
}
