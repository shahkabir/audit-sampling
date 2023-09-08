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
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Starter Page</li>
            </ol>
          </div><!-- /.col --> --}}
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    
    <form name="generateSample" action="{{URL::to('generate-sampling')}}" method="post">
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
                  <label>Date Range</label>

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
                      <div class="col-3">
                        <label>Audit per Day</label>
                        {{-- Audit Target --}}
                      </div>
                      
                      <div class="col-3">
                        <label>No. Of Agent</label>
                      </div>

                      <div class="col-3">
                        <label>Audit per Agent</label>
                      </div>

                      <div class="col-3">
                        <label>Audit Target</label>
                        {{-- Audit per Day --}}
                      </div>
                    </div>

                    <div class="row">
                        <div class="col-3">
                          <input type="text" class="form-control" id="audit-per-day" name="audit-target" value="" placeholder="Audit per Day" readonly>
                          {{--  --}}
                        </div>
                        
                        <div class="col-3">
                          <input type="text" class="form-control" id="no-of-agent" value="" name="no-of-agent" placeholder="No. Of Agent" required>
                        </div>

                        <div class="col-3">
                          <input type="text" class="form-control" id="audit-per-agent" name="audit-per-agent" value="" placeholder="Audit per Agent" required>
                        </div>

                        <div class="col-3">
                          <input type="text" class="form-control" id="audit-target" value="" placeholder="Audit Target" readonly>
                        </div>
                    </div>
                    
                    <h1></h1>

                    <div class="row">
                      <div class="col-3">
                        <label>Month</label>
                      </div>
                      
                      <div class="col-3">
                        <label>Day</label>
                      </div>

                      <div class="col-3">
                        <label>Month To Date</label>
                      </div>

                      <div class="col-3">
                        <label></label>
                      </div>
                    </div>


                    <div class="row">

                      <div class="col-3">
                        <input type="text" class="form-control" id="mon" value="{{ date('F') }}" placeholder="Month" readonly>
                      </div>

                      <div class="col-3">
                        <input type="text" class="form-control" id="day" value="{{ date('d') }}" placeholder="Day">
                      </div>

                      <div class="col-3">
                        <input type="text" class="form-control" id="mtd" value="" placeholder="Month To Date">
                      </div>

                      <button type="submit" class="btn btn-primary">Generate</button>

                    </div>

                    <h1></h1>

                    <div class="row">
                      <div class="col-3">
                      </div>

                      <div class="col-3">
                      </div>

                      <div class="col-3">
                        <label>Total CUSTOM TARGET(%)</label>
                      </div>

                    </div>

                    <div class="row">
                      <div class="col-3">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" id="setZero" class="custom-control-input"/>
                          <label class="custom-control-label" for="setZero">Set CUSTOM TARGET=0%</label>
                        </div>
                      </div>

                      <div class="col-3">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" id="setfromAudit" class="custom-control-input"/>
                          <label class="custom-control-label" for="setfromAudit">Set All from AUDIT TARGET(%)</label>
                        </div>
                      </div>

                      <div class="col-3">
                        <input type="text" class="form-control" id="totalCustom" value="" readonly>
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

          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">

                <table class="table table-bordered">
                  <thead>
                    <tr style="background-color:#ffb38a">
                      <th>CRITERIA</th>
                      <th>%</th>
                      <th style="width: 40px">COUNT</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      
                      <td>{{$parameters[5]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[5]->sampling_value_in_percent}}' id='sc-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='sc-val' name='sc-val' size='3' readonly></td>
                    </tr>
                    <tr>
                      
                      <td>{{$parameters[6]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[6]->sampling_value_in_percent}}' id='mc-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='mc-val' name='mc-val' size='3' readonly></td>
                    </tr>

                    <tr>
                      <td>{{$parameters[7]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[7]->sampling_value_in_percent}}' id='lc-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='lc-val' name='lc-val' size='3' readonly></td>
                    </tr>

                    <tr>
                      <td>{{$parameters[8]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[8]->sampling_value_in_percent}}' id='uc-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='uc-val' name='uc-val' size='3' readonly></td>
                    </tr>

                    <tr>
                      
                      <td colspan="2">Total</td>
                      
                      <td><input type='text' value='0' id='dur-total' name='dur-total' size='4' readonly></td>
                    </tr>
                    
                  </tbody>
                </table>

                <table class="table table-bordered">
                  <thead>
                    {{-- <tr colspan="3">OUTCOME WISE</tr> --}}
                    <tr style="background-color: #ff9248">
                      <th>CRITERIA</th>
                      <th>%</th>
                      <th style="width: 40px">COUNT</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>{{$parameters[0]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[0]->sampling_value_in_percent}}' id='info-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='info-val' name='info-val' size='3' readonly></td></td>
                    </tr>
                    <tr>
                      
                      <td>{{$parameters[1]->samplingCriteria}}</td>
                      <td>
                        
                          <input type='text' value='{{$parameters[1]->sampling_value_in_percent}}' id='sad-per' name='' size='1' disabled>
                        
                      </td>
                      <td><input type='text' value='0' id='sad-val' name='sad-val' size='3' readonly></td>
                    </tr>
                    <tr>
                      <td>{{$parameters[2]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[2]->sampling_value_in_percent}}' id='comp-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='comp-val' name='comp-val' size='3' readonly></td>
                    </tr>
                    <tr>

                      <tr>
                        <td>{{$parameters[3]->samplingCriteria}}</td>
                        <td>
                          <input type='text' value='{{$parameters[3]->sampling_value_in_percent}}' id='bald-per' name='' size='1' disabled>
                        </td>
                        <td><input type='text' value='0' id='bald-val' name='bald-val' size='3' readonly></td>
                      </tr>
                      <tr>
                      
                      <td>{{$parameters[4]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[4]->sampling_value_in_percent}}' id='cfl-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='cfl-val' name='cfl-val' size='3' readonly></td>
                    </tr>

                    <tr>
                      
                      <td colspan="2">Total</td>
                      
                      <td><input type='text' value='0' id='out-total' name='out-total' size='4' readonly></td>
                    </tr>

                  </tbody>
                </table>

                

                <table class="table table-bordered">
                  <thead>
                    <tr style="background-color:#ffd7b5">
                      <th>CRITERIA</th>
                      <th>%</th>
                      <th style="width: 40px">COUNT</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      
                      <td>{{$parameters[9]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[9]->sampling_value_in_percent}}' id='pb-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='pb-val' name='pb-val' size='3' readonly></td>
                    </tr>
                    <tr>
                      
                      <td>{{$parameters[10]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[10]->sampling_value_in_percent}}' id='gr-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='gr-val' name='gr-val' size='3' readonly></td>
                    </tr>
                    <tr>
                      
                      <td>{{$parameters[11]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[11]->sampling_value_in_percent}}' id='sl-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='sl-val' name='sl-val' size='3' readonly></td>
                    </tr>
                    <tr>
                      <td>{{$parameters[12]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[12]->sampling_value_in_percent}}' id='bl-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='bl-val' name='bl-val' size='3' readonly></td>
                    </tr>

                    <tr>
                      <td>{{$parameters[13]->samplingCriteria}}</td>
                      <td>
                        <input type='text' value='{{$parameters[13]->sampling_value_in_percent}}' id='ot-per' name='' size='1' disabled>
                      </td>
                      <td><input type='text' value='0' id='ot-val' name='ot-val' size='3' readonly></td>
                    </tr>

                    <tr>
                      
                      <td colspan="2">Total</td>
                      
                      <td><input type='text' value='0' id='seg-total' name='seg-total' size='4' readonly></td>
                    </tr>

                  </tbody>
                </table>

                  
              </div>
            </div>
          </div>
        

          
          {{-- </div> --}}
          <!-- /.col-md-6 -->

          <!-- /.col-md-6 -->
          <div class="col-lg-8">
            <div class="card">
              {{-- <div class="card-header">
                <h3 class="card-title">DataTable with default features</h3>
              </div>
              <!-- /.card-header --> --}}
              <div class="card-body">

                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>CODE</th>
                    {{-- <th>CATEGORY</th> --}}
                    <th>QUERY</th>
                    <th>TOTAL(01 till Yesterday)</th>
                    <th>AUDIT TARGET(%)</th>
                    <th>CUSTOM TARGET(%)</th>
                    {{--  --}}
                    <th>DAILY TARGET</th>

                    <th>MTD Target</th>
                    <th>MTD Done</th>
                    <th>Pending</th>
                  </tr>
                  </thead>
                  <tbody>
                    @foreach ($wcdata as $wc)
                  <tr>
                    <td>{{$wc->wc}}</td>
                    {{-- <td>{{$wc->category}}</td> --}}
                    <td>{{$wc->query}}</td>
                    <td>{{$wc->count_till_date}}</td>
                    <td><input type='text' class="form-control" name='' id='{{'atp-'.$wc->wc}}' value='{{$wc->audit_target_in_per}}' size='5' disabled /></td>
                    <td><input type='text' class="form-control" name='{{'catp-'.$wc->wc}}' id='{{'catp-'.$wc->wc}}' value="{{$wc->target_per}}" size='4'/></td>
                    
                    <td><input type='text' class="form-control" name='{{'wc-'.$wc->wc}}' id='{{'mt-'.$wc->wc}}' value='' size='3' readonly/></td>

                    <td><input type='text' class="form-control" name='' id='{{'mtt-'.$wc->wc}}' value='' size='5' readonly/></td>
                    <td><input type='text' class="form-control" name='' id='' value='' size='3' readonly /></td>
                    <td><input type='text' class="form-control" name='' id='' value='' size='3' readonly /></td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                  {{-- <tfoot>
                  <tr>
                    <th>Rendering engine</th>
                    <th>Browser</th>
                    <th>Platform(s)</th>
                    <th>Engine version</th>
                    <th>CSS grade</th>
                  </tr>
                  </tfoot> --}}
                </table>
              </div>
              <!-- /.card-body -->            
            </div>
            <!-- /.card -->
          {{-- </form> --}}
            {{-- <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="card-title m-0">Featured</h5>
              </div>
              <div class="card-body">
                <h6 class="card-title">Special title treatment</h6>

                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
              </div>
            </div> --}}
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
      scrollY: "750px",

      columnDefs: [
            { "width": "12px", "targets": "1" }
        ],

	  //"bAutoWidth": false,

      "buttons": ["copy", "csv", "excel", "pdf"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


    // $('#example1').DataTable({
    
    //   "lengthChange": false,
    //   "searching": false,
    
    
    //   "autoWidth": false,
    //   "responsive": true,
    // });
  

    //Code for sample calculation
    $('#audit-target, #mtd').on('input',function(){
        
        //var auditTarget = $(this).val();
        var monthlyAuditTarget = $('#audit-target').val();
        var auditTargetPerDay = Math.round(monthlyAuditTarget/30); //$('#day').val()
        $('#audit-per-day').val(auditTargetPerDay);

        var auditTarget = auditTargetPerDay;

        //console.log(auditTarget);
        

        $('#info-val').val(getCount($('#info-per').val(), auditTarget));
        $('#sad-val').val(getCount($('#sad-per').val(),auditTarget));
        $('#comp-val').val(getCount($('#comp-per').val(),auditTarget));
        $('#bald-val').val(getCount($('#bald-per').val(),auditTarget));
        $('#cfl-val').val(getCount($('#cfl-per').val(),auditTarget));

        $('#out-total').val(
          parseFloat($('#info-val').val()) 
          + parseFloat($('#sad-val').val())
          + parseFloat($('#comp-val').val())
          + parseFloat($('#bald-val').val())
          + parseFloat($('#cfl-val').val())
          );

        $('#sc-val').val(getCount($('#sc-per').val(),auditTarget));
        $('#mc-val').val(getCount($('#mc-per').val(),auditTarget));
        $('#lc-val').val(getCount($('#lc-per').val(),auditTarget));
        $('#uc-val').val(getCount($('#uc-per').val(),auditTarget));

        $('#dur-total').val(
          parseFloat($('#sc-val').val()) 
          + parseFloat($('#mc-val').val())
          + parseFloat($('#lc-val').val())
          + parseFloat($('#uc-val').val())
        );

        $('#pb-val').val(getCount($('#pb-per').val(), auditTarget));
        $('#gr-val').val(getCount($('#gr-per').val(),auditTarget));
        $('#sl-val').val(getCount($('#sl-per').val(),auditTarget));
        $('#bl-val').val(getCount($('#bl-per').val(),auditTarget));
        $('#ot-val').val(getCount($('#ot-per').val(),auditTarget));

        $('#seg-total').val(
          parseFloat($('#pb-val').val()) 
          + parseFloat($('#gr-val').val())
          + parseFloat($('#sl-val').val())
          + parseFloat($('#bl-val').val())
          + parseFloat($('#ot-val').val())
          );


        //Update Daily Target input field
        // Selector for input fields to be updated
        var inputFieldsToUpdate = $("input[id^='mt-']");
        var inputFieldsToUpdateMTD = $("input[id^='mtt-']");
        var inputFieldsToUpdateCustomTarget = $("input[id^='catp-']");

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
            var inputFieldToUpdateCustomTarget = inputFieldsToUpdateCustomTarget.eq(index);

            if(jQuery.isNumeric(inputFieldToUpdateCustomTarget.val()))
            {
              var monthlyTarget = Math.round((inputFieldToUpdateCustomTarget.val()/100)*auditTarget);
            }else{
              // Update the value of the input field
              var monthlyTarget = Math.round((newValue/100)*auditTarget);
            }

            //console.log('monthlyTarget:'+monthlyTarget);

            inputFieldToUpdate.val(monthlyTarget);
            //inputFieldToUpdateMTD.val((Math.round(monthlyTarget/day)*mtd));
            inputFieldToUpdateMTD.val(monthlyTarget*mtd);
          });
    });

    
        //Custom Target
        var totalCustomInput = $("#totalCustom");
        var customTargetFields = $("input[id^='catp-']");

          customTargetFields.on('input',function(){
            
            var auditTarget = $('#audit-per-day').val();

            var catpId = $(this).attr('id');
            var catpValue = parseFloat($(this).val());
            var mtId = catpId.replace('catp-', 'mt-');
            var updatedValue = Math.round((catpValue/100) * auditTarget);
            //console.log('updatedValue:'+updatedValue);

            if(!isNaN(updatedValue)){
              $('#' + mtId).val(updatedValue);
            }else{
              //$('#audit-per-day').trigger('input');
              var auditTargetPer = parseFloat($(this).closest('tr').find('input[id^="atp-"]').val());
              //console.log('auditTarget:'+auditTargetPer);
              $('#' + mtId).val(Math.round((auditTargetPer/100) * auditTarget));
            }

              //Now set the total in custom field
              var total = 0;
              customTargetFields.each(function() {
                var value = parseFloat($(this).val());
                if (!isNaN(value)) {
                  total += value;
                }

              });

              totalCustomInput.val(total);
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
    
    //Set Custom Target to 0
    $("#setZero").on("change", function() {
      if ($(this).is(":checked")) {
        $("input[id^='catp-']").val(0);
        $('#totalCustom').val(0);

        $('#audit-target').trigger('input');
        
      }
    });

    //Set Custom Target to blank
    $("#setfromAudit").on("change", function() {
      if ($(this).is(":checked")) {
        $("input[id^='catp-']").val('');
        $('#totalCustom').val('');

        $('#audit-target').trigger('input');
        
      }
    });


    function getCount(percent, auditTarget)
    {
      var constant = 100;
      return Math.round(((percent/constant)*auditTarget));//toFixed(2).replace(/\.?0*$/, '')
    }

    //Date range picker
    $('#reservation').daterangepicker();
    
  });
</script>
@endsection