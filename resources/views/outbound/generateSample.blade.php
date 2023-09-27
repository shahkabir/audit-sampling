@extends('layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <div class="content-header">
     <div class="container-fluid">
       <div class="row mb-2">
         <div class="col-sm-6">
           <!-- <h1 class="m-0">Starter Page</h1> -->
         </div><!-- /.col -->
         {{-- <div class="col-sm-6">
           <ol class="breadcrumb float-sm-right">
             <li class="breadcrumb-item"><a href="#">OUTBOUND</a></li>
             <li class="breadcrumb-item active">OUTBOUND</li>
           </ol>
         </div><!-- /.col --> --}}
       </div><!-- /.row -->
     </div><!-- /.container-fluid -->
   </div>
   <!-- /.content-header -->
   {{-- {{dd($auditPerAgent)}} --}}
   <form name="generateSample" action="{{URL::to('generate-sampling-outbound')}}" method="post" id="generate-form">
     @csrf
   <!-- Main content -->
   <div class="content">
     <div class="container">
       {{-- container-fluid --}}
       <div class="row">
         <div class="col-lg-12">
           <div class="card">
             <div class="card-body">
               <!-- <h5 class="card-title">Card title</h5>

               <p class="card-text">
                 Some quick example text to build on the card title and make up the bulk of the card's
                 content.
               </p> -->
               
                <!-- Date range -->
                <div class="form-group">
                 <label>Date Range:</label>

                 <div class="input-group">
                   <div class="input-group-prepend">
                     <span class="input-group-text">
                       <i class="far fa-calendar-alt"></i>
                     </span>
                   </div>
                   <input type="text" class="form-control float-left .col-4" id="reservation" name="date_range">
                   </div>
                   <!-- /.input group -->
                   </div>
                   <!-- /.form group -->

                   <div class="row">
                     {{-- <div class="col-3">
                       <label>Audit per Day</label>
                       {{-- Audit Target --}}
                     {{--</div>--}}
                     
                     <div class="col-3">
                       <label>Sample Size</label>
                     </div>

                     <div class="col-3">
                       <label>Audit per Agent</label>
                     </div>

                     <div class="col-3">
                       <label>Monthly Target</label>
                       {{-- Audit per Day --}}
                     </div>
                 </div>

                   <div class="row">
                       {{-- <div class="col-3">
                         <input type="text" class="form-control" id="audit-per-day" name="audit-target" value="" placeholder="Audit per Day" readonly>
                         {{--  --}}
                       {{--</div> --}}
                       
                       <div class="col-3">
                         <input type="text" class="form-control" id="no-of-agent" value="" name="no-of-agent" placeholder="Sample Size">
                       </div>

                       <div class="col-3">
                         <input type="text" class="form-control" id="audit-per-agent" value="{{ $auditPerAgent[0]->value }}" name="audit-per-agent" placeholder="Audit per Agent" readonly>
                       </div>

                       <div class="col-3">
                         <input type="text" class="form-control" id="audit-target" value="" placeholder="Audit Target" readonly>
                       </div>

                       <div class="col-3">
                        <button type="submit" class="btn btn-primary" >Generate</button>
                     </div>  

                   </div>
                   
                   <h1></h1>

                   <div class="row">
                    <div class="col-3">   <label></label>                   
                    </div>           
                  </div>
                  

                   <div class="row">

                    <div class="col-3">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" name="set-Ticket-Category" id="set-Ticket-Category" class="custom-control-input" checked="checked"/>
                        <label class="custom-control-label" for="set-Ticket-Category">Set Ticket Category</label>
                      </div>
                    </div>

                    <div class="col-3">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" name="set-Ticket-Status" id="set-Ticket-Status" class="custom-control-input" checked="checked"/>
                        <label class="custom-control-label" for="set-Ticket-Status">Set Ticket Status</label>
                      </div>
                    </div>

                    <div class="col-3">
                      <div class="custom-control custom-switch">
                        <input type="checkbox" name="set-Channel" id="set-Channel" class="custom-control-input" checked="checked"/>
                        <label class="custom-control-label" for="set-Channel">Set Channel</label>
                      </div>
                    </div>

                   </div>

                   <div class="row">
                     <div class="col-12">
                         @if (session('success'))
                           <div class="alert alert-success">
                               {{ session('success') }}
                           </div>
                         @endif

                         @if ($errors->any())
                           <div class="alert alert-danger">
                               <ul>
                                   @foreach ($errors->all() as $error)
                                       <li>{{ $error }}</li>
                                   @endforeach
                               </ul>
                           </div>
                         @endif

                     </div>
                   </div>
                 </div>

                 {{-- {{dd($parameters)}} --}}
           </div>

         </div>

         <div class="col-lg-6">
           <div class="card">
             <div class="card-body">
               <table class="table table-bordered table-striped">
                 <thead>
                   {{-- <tr colspan="3">OUTCOME WISE</tr> --}}
                   <tr style="background-color: #ff9248">
                     <th rowspan="2">Ticket Category</th>
                     <th>%</th>
                     <th rowspan="2" style="width: 40px">COUNT</th>
                   </tr>

                   <tr style="background-color: #ff9248">
                    <th>
                        <div class="custom-control custom-switch">
                          <input type="checkbox" name="set-Ticket-Category-zero" id="set-Ticket-Category-zero" class="custom-control-input"/>
                          <label class="custom-control-label" for="set-Ticket-Category-zero">Set 0</label>
                        </div>
                    </th>
                  </tr>

                 </thead>
                 <tbody>
                   <tr>
                     <td>{{$parameters[0]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[0]->sampling_value_in_percent}}' id='tc-nqc-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-nqc-val' name='nqc-val' size='3' readonly></td>
                   </tr>
                   <tr>
                     
                     <td>{{$parameters[1]->samplingCriteria}}</td>
                     <td>
                         <input class="form-control" type='text' value='{{$parameters[1]->sampling_value_in_percent}}' id='tc-oth-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-oth-val' name='oth-val' size='3' readonly></td>
                   </tr>
                   <tr>
                     <td>{{$parameters[2]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[2]->sampling_value_in_percent}}' id='tc-src-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-src-val' name='src-val' size='3' readonly></td>
                   </tr>
                   <tr>

                     <tr>
                       <td>{{$parameters[3]->samplingCriteria}}</td>
                       <td>
                         <input class="form-control" type='text' value='{{$parameters[3]->sampling_value_in_percent}}' id='tc-prc-per' name='' size='3'>
                       </td>
                       <td><input class="form-control" type='text' value='0' id='tc-prc-val' name='prc-val' size='3' readonly></td>
                     </tr>

                     <tr>
                     <td>{{$parameters[4]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[4]->sampling_value_in_percent}}' id='tc-irc-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-irc-val' name='irc-val' size='3' readonly></td>
                   </tr>

                 
                   <tr>
                     <td>{{$parameters[5]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[5]->sampling_value_in_percent}}' id='tc-pcc-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-pcc-val' name='pcc-val' size='3' readonly></td>
                   </tr>
                   <tr>
                     
                     <td>{{$parameters[6]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[6]->sampling_value_in_percent}}' id='tc-vasc-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-vasc-val' name='vasc-val' size='3' readonly></td>
                   </tr>

                   <tr>
                     <td>{{$parameters[7]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[7]->sampling_value_in_percent}}' id='tc-sr-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-sr-val' name='sr-val' size='3' readonly></td>
                   </tr>

                   <tr>
                     <td>{{$parameters[8]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[8]->sampling_value_in_percent}}' id='tc-rcc-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-rcc-val' name='rcc-val' size='3' readonly></td>
                   </tr>

                   
                   <tr>
                     <td>{{$parameters[9]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[9]->sampling_value_in_percent}}' id='tc-pbc-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-pbc-val' name='pbc-val' size='3' readonly></td>
                   </tr>
                   <tr>
                     
                     <td>{{$parameters[10]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[10]->sampling_value_in_percent}}' id='tc-toffee-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-toffee-val' name='toffee-val' size='3' readonly></td>
                   </tr>
                   <tr>
                     
                     <td>{{$parameters[11]->samplingCriteria}}</td>
                     <td>
                       <input class="form-control" type='text' value='{{$parameters[11]->sampling_value_in_percent}}' id='tc-csc-per' name='' size='3'>
                     </td>
                     <td><input class="form-control" type='text' value='0' id='tc-csc-val' name='csc-val' size='3' readonly></td>
                   </tr>
                   
                   <tr>
                    <td colspan="2">Total</td>
                    <td><input class="form-control" type='text' value='0' id='ticket-total' name='ticket-total' size='4' readonly></td>
                  </tr>

                </tbody>
              </table>                 
             </div>
           </div>
         </div>
       

         
         {{-- </div> --}}
         <!-- /.col-md-6 -->

         <!-- /.col-md-6 -->
         <div class="col-lg-6">
           <div class="card">
             {{-- <div class="card-header">
               <h3 class="card-title">DataTable with default features</h3>
             </div>
             <!-- /.card-header --> --}}
             <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead>
                      <tr style="background-color: #FF7417">
                        <th rowspan="2">Ticket Status</th>
                        <th>%</th>
                        <th rowspan="2" style="width: 40px">COUNT</th>
                      </tr>
                      

                      <tr style="background-color: #FF7417">
                        <th>
                            <div class="custom-control custom-switch">
                              <input type="checkbox" name="set-Ticket-Status-zero" id="set-Ticket-Status-zero" class="custom-control-input"/>
                              <label class="custom-control-label" for="set-Ticket-Status-zero">Set 0</label>
                            </div>
                        </th>
                      </tr>
                    </thead>
                <tbody>
    
                  <tr>
                    <td>{{$parameters[12]->samplingCriteria}}</td>
                    <td>
                      <input class="form-control" type='text' value='{{$parameters[12]->sampling_value_in_percent}}' id='ts-ass-per' name='' size='3'>
                    </td>
                    <td><input class="form-control" type='text' value='0' id='ts-ass-val' name='ts-ass-val' size='3' readonly></td>
                  </tr>
                       <tr>
                         <td>{{$parameters[13]->samplingCriteria}}</td>
                         <td>
                           <input class="form-control" type='text' value='{{$parameters[13]->sampling_value_in_percent}}' id='ts-tf-per' name='' size='3'>
                         </td>
                         <td><input class="form-control" type='text' value='0' id='ts-tf-val' name='ts-tf-val' size='3' readonly></td>
                       </tr>

                       <tr>
                        <td>{{$parameters[14]->samplingCriteria}}</td>
                        <td>
                          <input class="form-control" type='text' value='{{$parameters[14]->sampling_value_in_percent}}' id='ts-re-per' name='' size='3'>
                        </td>
                        <td><input class="form-control" type='text' value='0' id='ts-re-val' name='ts-re-val' size='3' readonly></td>
                      </tr>

                      <tr>
                        <td>{{$parameters[15]->samplingCriteria}}</td>
                        <td>
                          <input class="form-control" type='text' value='{{$parameters[15]->sampling_value_in_percent}}' id='ts-fl-per' name='' size='3'>
                        </td>
                        <td><input class="form-control" type='text' value='0' id='ts-fl-val' name='ts-fl-val' size='3' readonly></td>
                      </tr>

                      <tr>
                        <td>{{$parameters[16]->samplingCriteria}}</td>
                        <td>
                          <input class="form-control" type='text' value='{{$parameters[16]->sampling_value_in_percent}}' id='ts-cl-per' name='' size='3'>
                        </td>
                        <td><input class="form-control" type='text' value='0' id='ts-cl-val' name='ts-cl-val' size='3' readonly></td>
                      </tr>
    
                       <tr>
                         <td colspan="2">Total</td>
                         <td><input class="form-control" type='text' value='0' id='ts-total' name='ts-total' size='4' readonly></td>
                       </tr>
    
                     </tbody>
                   </table>

                   <table class="table table-bordered">
                    <thead>
                      {{-- <tr colspan="3">OUTCOME WISE</tr> --}}
                      <tr style="background-color: #FD6A02">
                        <th rowspan="2">Channel</th>
                        <th>%</th>
                        <th rowspan="2" style="width: 40px">COUNT</th>
                      </tr>

                      <tr style="background-color: #FD6A02">
                        <th>
                            <div class="custom-control custom-switch">
                              <input type="checkbox" name="set-Ticket-Channel-zero" id="set-Ticket-Channel-zero" class="custom-control-input"/>
                              <label class="custom-control-label" for="set-Ticket-Channel-zero">Set 0</label>
                            </div>
                        </th>
                      </tr>

                    </thead>
                    <tbody>
                        <tr>
                            <td>{{$parameters[17]->samplingCriteria}}</td>
                            <td>
                            <input class="form-control" type='text' value='{{$parameters[17]->sampling_value_in_percent}}' id='ch-inb-per' name='' size='3'>
                            </td>
                            <td><input class="form-control" type='text' value='0' id='ch-inb-val' name='ch-inb-val' size='3' readonly></td>
                        </tr>

                        <tr>
                            <td>{{$parameters[18]->samplingCriteria}}</td>
                            <td>
                            <input class="form-control" type='text' value='{{$parameters[18]->sampling_value_in_percent}}' id='ch-app-per' name='' size='3'>
                            </td>
                            <td><input class="form-control" type='text' value='0' id='ch-app-val' name='ch-app-val' size='3' readonly></td>
                        </tr>

                        <tr>
                            <td>{{$parameters[19]->samplingCriteria}}</td>
                            <td>
                            <input class="form-control" type='text' value='{{$parameters[19]->sampling_value_in_percent}}' id='ch-ivr-per' name='' size='3'>
                            </td>
                            <td><input class="form-control" type='text' value='0' id='ch-ivr-val' name='ch-ivr-val' size='3' readonly></td>
                        </tr>

                        <tr>
                            <td>{{$parameters[20]->samplingCriteria}}</td>
                            <td>
                            <input class="form-control" type='text' value='{{$parameters[20]->sampling_value_in_percent}}' id='ch-ussd-per' name='' size='3'>
                            </td>
                            <td><input class="form-control" type='text' value='0' id='ch-ussd-val' name='ch-ussd-val' size='3' readonly></td>
                        </tr>

                        <tr>
                            <td>{{$parameters[21]->samplingCriteria}}</td>
                            <td>
                            <input class="form-control" type='text' value='{{$parameters[21]->sampling_value_in_percent}}' id='ch-mono-per' name='' size='3'>
                            </td>
                            <td><input class="form-control" type='text' value='0' id='ch-mono-val' name='ch-mono-val' size='3' readonly></td>
                        </tr>

                        <tr>
                            <td>{{$parameters[22]->samplingCriteria}}</td>
                            <td>
                            <input class="form-control" type='text' value='{{$parameters[22]->sampling_value_in_percent}}' id='ch-oth-per' name='' size='3'>
                            </td>
                            <td><input class="form-control" type='text' value='0' id='ch-oth-val' name='ch-oth-val' size='3' readonly></td>
                        </tr>

                        <tr>
                            <td colspan="2">Total</td>
                            <td><input class="form-control" type='text' value='0' id='ch-total' name='ch-total' size='4' readonly></td>
                        </tr>

                    </tbody>
                </table>
             </div>
             <!-- /.card-body -->            
           </div>
           <!-- /.card -->
        
         </div>
         <!-- /.col-md-6 -->

       </div>
       <!-- /.row -->
     </div><!-- /.container-fluid -->
   </div>
   <!-- /.content -->
 </form>
 </div>
 <!-- /.content-wrapper -->

 <!-- Page specific script -->
<script>
 $(function () {

   $("#example1").DataTable({
     pageLength: -1,
     scrollX: true,
     scrollCollapse: true,
     order: [[3, 'desc']],
     //"responsive": true, 
     // "lengthChange": false, 
     autoWidth: true,
     paging: true,
     // "ordering": false,
     //"info": false,

     //"order": false,
       bLengthChange: false,
     scrollY: "700px",

     columnDefs: [
           { "width": "10px", "targets": "1" }
       ],

     //"bAutoWidth": false,

     // "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
   });//.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


   // $('#example1').DataTable({
   
   //   "lengthChange": false,
   //   "searching": false,
   
   
   //   "autoWidth": false,
   //   "responsive": true,
   // });

   //Set Custom Target to 0
   $("#set-Ticket-Status-zero").on("change", function() {
      if ($(this).is(":checked")) {
        $("input[id^='ts-']").val(0);
        $('#ts-total').val(0);
        $('#no-of-agent').trigger('input');
      }
    });

    $("#set-Ticket-Channel-zero").on("change", function() {
      if ($(this).is(":checked")) {
        $("input[id^='ch-']").val(0);
        $('#ch-total').val(0);
        $('#no-of-agent').trigger('input');
      }
    });

    $("#set-Ticket-Category-zero").on("change", function() {
      if ($(this).is(":checked")) {
        $("input[id^='tc-']").val(0);
        $('#ticket-total').val(0);
        $('#no-of-agent').trigger('input');
      }
    });
 
   //Code for form validation
   $("#generate-form").submit(function(event) {
        // Get all checkboxes
        var checkboxes = $(this).find("input[type='checkbox']");// need to take only the 3 checkboxes

        // Check if at least one checkbox is checked
        var isAtLeastOneChecked = checkboxes.is(":checked");

        if (!isAtLeastOneChecked) {
            event.preventDefault(); // Prevent form submission
            alert("Please select at least one Sample Category.");
        }

        var auditTarget = $('#no-of-agent').val();
        var auditTarget = parseInt(auditTarget);
        if ((isNaN(auditTarget) || !auditTarget || auditTarget <= 0)) {
            event.preventDefault(); // Prevent form submission
            alert("Please provide correct value in Sample Size.");
        }

        //Check if all Totals are equal to Sample Size
        var auditTarget = parseInt($('#no-of-agent').val(), 10);
        var tsTotal = parseInt($('#ts-total').val(), 10);
        var chTotal = parseInt($('#ch-total').val(), 10);
        var catTotal = parseInt($('#ticket-total').val(), 10);

        // if(catTotal !== auditTarget){
        //   event.preventDefault(); // Prevent form submission
        //   alert("Ticket Category total is not equal to Sample Size value.");
        // }else if(chTotal !== auditTarget){
        //   event.preventDefault(); // Prevent form submission
        //   alert("Channel total is not equal to Sample Size value.");
        // }else if(tsTotal !== auditTarget){
        //   event.preventDefault(); // Prevent form submission
        //   alert("Ticket Status total is not equal to Sample Size value.");
        // }

    });

   //Code for sample calculation
   $('#audit-target').on('input',function(){
       
       //var auditTarget = $(this).val();
       var monthlyAuditTarget = $('#no-of-agent').val();
       var auditTargetPerDay = Math.round(monthlyAuditTarget/$('#day').val()); 
       $('#audit-per-day').val(auditTargetPerDay);

       //var auditTarget = auditTargetPerDay;

       var auditTarget = monthlyAuditTarget;

       //console.log(auditTarget);
       
        $('#tc-nqc-val').val(getCount($('#tc-nqc-per').val(),auditTarget));
        $('#tc-oth-val').val(getCount($('#tc-oth-per').val(),auditTarget));
        $('#tc-src-val').val(getCount($('#tc-src-per').val(),auditTarget));
        $('#tc-prc-val').val(getCount($('#tc-prc-per').val(),auditTarget));
        $('#tc-irc-val').val(getCount($('#tc-irc-per').val(),auditTarget));
        $('#tc-pcc-val').val(getCount($('#tc-pcc-per').val(),auditTarget));
        $('#tc-vasc-val').val(getCount($('#tc-vasc-per').val(),auditTarget));
        $('#tc-sr-val').val(getCount($('#tc-sr-per').val(),auditTarget));
        $('#tc-rcc-val').val(getCount($('#tc-rcc-per').val(),auditTarget));
        $('#tc-pbc-val').val(getCount($('#tc-pbc-per').val(),auditTarget));
        $('#tc-toffee-val').val(getCount($('#tc-toffee-per').val(),auditTarget));
        $('#tc-csc-val').val(getCount($('#tc-csc-per').val(),auditTarget));
        
        // $('#out-total').val(
        //  parseFloat($('#info-val').val()) 
        //  + parseFloat($('#sad-val').val())
        //  + parseFloat($('#comp-val').val())
        //  + parseFloat($('#bald-val').val())
        //  + parseFloat($('#cfl-val').val())
        //  );

        $('#ticket-total').val( 
                getCount($('#tc-nqc-per').val(),auditTarget, false)+
                getCount($('#tc-oth-per').val(),auditTarget, false)+
                getCount($('#tc-src-per').val(),auditTarget, false)+
                getCount($('#tc-prc-per').val(),auditTarget, false)+
                getCount($('#tc-irc-per').val(),auditTarget, false)+
                getCount($('#tc-pcc-per').val(),auditTarget, false)+
                getCount($('#tc-vasc-per').val(),auditTarget, false)+
                getCount($('#tc-sr-per').val(),auditTarget, false)+
                getCount($('#tc-rcc-per').val(),auditTarget, false)+
                getCount($('#tc-pbc-per').val(),auditTarget, false)+
                getCount($('#tc-toffee-per').val(),auditTarget, false)+
                getCount($('#tc-csc-per').val(),auditTarget, false)

              // parseFloat($('#tc-nqc-val').val()) +
              // parseFloat($('#tc-oth-val').val()) +
              // parseFloat($('#tc-src-val').val()) +
              // parseFloat($('#tc-prc-val').val()) +
              // parseFloat($('#tc-irc-val').val()) +
              // parseFloat($('#tc-pcc-val').val()) +
              // parseFloat($('#tc-vasc-val').val()) +
              // parseFloat($('#tc-sr-val').val()) +
              // parseFloat($('#tc-rcc-val').val()) +
              // parseFloat($('#tc-pbc-val').val()) +
              // parseFloat($('#tc-toffee-val').val()) +
              // parseFloat($('#tc-csc-val').val())
        );

        console.log($('#ticket-total').val());

        $('#ts-ass-val').val(getCount($('#ts-ass-per').val(),auditTarget));
        $('#ts-tf-val').val(getCount($('#ts-tf-per').val(),auditTarget));
        $('#ts-re-val').val(getCount($('#ts-re-per').val(),auditTarget));
        $('#ts-fl-val').val(getCount($('#ts-fl-per').val(),auditTarget));
        $('#ts-cl-val').val(getCount($('#ts-cl-per').val(),auditTarget));

       $('#ts-total').val(
            getCount($('#ts-ass-per').val(),auditTarget, false)+
            getCount($('#ts-tf-per').val(),auditTarget, false)+
            getCount($('#ts-re-per').val(),auditTarget, false)+
            getCount($('#ts-fl-per').val(),auditTarget, false)+
            getCount($('#ts-cl-per').val(),auditTarget, false)
        );

        console.log($('#ts-total').val());

        //  parseFloat($('#ts-ass-val').val()) 
        //  + parseFloat($('#ts-tf-val').val())
        //  + parseFloat($('#ts-re-val').val())
        //  + parseFloat($('#ts-fl-val').val())
        //  + parseFloat($('#ts-cl-val').val())
       
       
    $('#ch-inb-val').val(getCount($('#ch-inb-per').val(),auditTarget));
    $('#ch-app-val').val(getCount($('#ch-app-per').val(),auditTarget));
    $('#ch-ivr-val').val(getCount($('#ch-ivr-per').val(),auditTarget));
    $('#ch-ussd-val').val(getCount($('#ch-ussd-per').val(),auditTarget));
    $('#ch-mono-val').val(getCount($('#ch-mono-per').val(),auditTarget));
    $('#ch-oth-val').val(getCount($('#ch-oth-per').val(),auditTarget));


       $('#ch-total').val(
          getCount($('#ch-inb-per').val(),auditTarget, false)+
          getCount($('#ch-app-per').val(),auditTarget, false)+
          getCount($('#ch-ivr-per').val(),auditTarget, false)+
          getCount($('#ch-ussd-per').val(),auditTarget, false)+
          getCount($('#ch-mono-per').val(),auditTarget, false)+
          getCount($('#ch-oth-per').val(),auditTarget, false)

        //  parseFloat($('#ch-inb-val').val()) 
        //  + parseFloat($('#ch-app-val').val())
        //  + parseFloat($('#ch-ivr-val').val())
        //  + parseFloat($('#ch-ussd-val').val())
        //  + parseFloat($('#ch-mono-val').val())
        //  + parseFloat($('#ch-oth-val').val())
         );


       //Update Monthly Target input field
       // Selector for input fields to be updated
       var inputFieldsToUpdate = $("input[id^='mt-']");
       var inputFieldsToUpdateMTD = $("input[id^='mtt-']");

       var day = $('#day').val();
       var mtd = $('#mtd').val();

       //var monthToDate = 

       // Selector for corresponding input fields with new values
       var correspondingInputFields = $("input[id^='atp-']");

       correspondingInputFields.each(function(index, element) {
           // Get the value of the corresponding input field
           var newValue = $(element).val();
           //console.log('newValue:'+newValue)
           
           // Use the index to identify the corresponding input field to update
           var inputFieldToUpdate = inputFieldsToUpdate.eq(index);
           var inputFieldToUpdateMTD = inputFieldsToUpdateMTD.eq(index);
           
           // Update the value of the input field
           var monthlyTarget = Math.round((newValue/100)*auditTarget);
           //console.log('monthlyTarget:'+monthlyTarget);

           inputFieldToUpdate.val(monthlyTarget);
           inputFieldToUpdateMTD.val((Math.round(monthlyTarget/day)*mtd));
         });
   });

   
   //Custom Target for Ticket Category
  var customTargetFields = $("input[id^='tc-']");

      customTargetFields.on('input',function(){ //index, element

         var auditTarget = $('#no-of-agent').val();

         var catpId = $(this).attr('id');
         var catpValue = parseFloat($(this).val());
         var mtId = catpId.replace('-per', '-val');
         var updatedValue = Math.round((catpValue/100) * auditTarget);
         
         $('#' + mtId).val(updatedValue);

         $('#no-of-agent').trigger('input');

      });
  
  //Custom Target for Ticket Status
  var customTargetFields = $("input[id^='ts-']");

      customTargetFields.on('input',function(){ //index, element

         var auditTarget = $('#no-of-agent').val();

         var catpId = $(this).attr('id');
         var catpValue = parseFloat($(this).val());
         var mtId = catpId.replace('-per', '-val');
         var updatedValue = Math.round((catpValue/100) * auditTarget);
         
         $('#' + mtId).val(updatedValue);

         $('#no-of-agent').trigger('input');

  });


  //Custom Target for Ticket Channel
  var customTargetFields = $("input[id^='ch-']");

      customTargetFields.on('input',function(){ //index, element

         var auditTarget = $('#no-of-agent').val();

         var catpId = $(this).attr('id');
         var catpValue = parseFloat($(this).val());
         var mtId = catpId.replace('-per', '-val');
         var updatedValue = Math.round((catpValue/100) * auditTarget);
         
         $('#' + mtId).val(updatedValue);

         $('#no-of-agent').trigger('input');

  });

       

   $('#no-of-agent').on('input',function(){

     var noOfAgent = $('#no-of-agent').val();
     var auditPerAgent = $('#audit-per-agent').val();

     $('#audit-target').val(noOfAgent*auditPerAgent);

     $('#audit-target').trigger('input');

     $('#audit-per-day').trigger('input');

   });


   $('#audit-per-agent').on('input',function(){

     var noOfAgent = $('#no-of-agent').val();
     var auditPerAgent = $('#audit-per-agent').val();

     $('#audit-target').val(noOfAgent*auditPerAgent);

     $('#audit-target').trigger('input');

     $('#audit-per-day').trigger('input');

     });

   function getCount(percent, auditTarget, roundup = true)
   {
     var constant = 100;
     
     if(roundup == true)
     {
      return Math.round(((percent/constant)*auditTarget));//toFixed(2).replace(/\.?0*$/, '')
     }else{
      return ((percent/constant)*auditTarget);
     }
      
   }

   //Date range picker
   $('#reservation').daterangepicker();
   
 });
</script>
@endsection