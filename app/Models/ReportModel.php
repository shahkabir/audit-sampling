<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportModel extends Model
{
    use HasFactory;

    public static function GetAssignedCalls($from,$to,$lob)
    {

        if($lob == 'IB')
            $table = 'assigned_ib';
        else if($lob == 'OB')
            $table = 'assigned_ob';

        $query = "select date(assignedDate) as dt, assignedTo, 
        count(*) as total_assigned, 
        sum(if(auditStatus=0,1,0)) as total_pending,
        sum(if(auditStatus=1,1,0)) as total_audited,
        sum(if(auditStatus=2,1,0)) as total_ignored
        from $table
        where date(assignedDate) between STR_TO_DATE('$from','%m/%d/%Y') and STR_TO_DATE('$to','%m/%d/%Y')
        group by date(assignedDate), assignedTo
        order by assignedTo asc";

        $results = DB::select($query);

        return $results;

    }

    public static function GetAssignedCallsDetails($from,$to, $lob)
    {

        if($lob == 'IB'){
            $query = "select assignedDate, assignedBy, assignedTo, parentID, CALLID,DURATION,SKILLNO,CODE,OUTCOME,MSISDN,
            case auditStatus
            when 0 then 'PENDING'
            when 1 then 'AUDITED'
            when 2 then 'IGNORED'
            else 'N/A'
            end as auditStatus,
            auditDate, ignoredComment
            from assigned_ib
            where date(assignedDate) between STR_TO_DATE('$from','%m/%d/%Y') and STR_TO_DATE('$to','%m/%d/%Y')
            order by assignedTo desc";

        }else if($lob == 'OB')
        {
            $query = "select assignedDate, assignedBy, assignedTo, parentID, SOID,CALLID_OB, MSISDN, STATUS, SOURCE, CATEGORY, SUBCATEGORY,
            case auditStatus
            when 0 then 'PENDING'
            when 1 then 'AUDITED'
            when 2 then 'IGNORED'
            else 'N/A'
            end as auditStatus,
            auditDate, ignoredComment
            from assigned_ob
            where date(assignedDate) between STR_TO_DATE('$from','%m/%d/%Y') and STR_TO_DATE('$to','%m/%d/%Y')
            order by assignedTo desc";
        }
        //dd($query);

        $results = DB::select($query);

        return $results;
    }
    
    public static function GetTargetvsGenerated($from,$to,$lob)
    {

        // if($lob == 'IB')
        //     $table = 'assigned_ib';
        // else if($lob == 'OB')
        //     $table = 'assigned_ob';


        $query = "select generated_by, created_at, target_audit, actual_outcome, change_in_percent
        from sampling_generation_history
        where date(created_at) between STR_TO_DATE('$from','%m/%d/%Y') and STR_TO_DATE('$to','%m/%d/%Y')
        and type = '$lob'
        order by created_at desc";

        $results = DB::select($query);

        return $results;
    }

    public static function GetSourceDataSummary($from,$to)
    {
        $query = "select data_date, source, filename, `count`, upload_datetime 
        from source_data_summary
        where data_date between STR_TO_DATE('$from','%m/%d/%Y') and STR_TO_DATE('$to','%m/%d/%Y')
        order by data_date desc";

        $results = DB::select($query);

        return $results;
    }
}
