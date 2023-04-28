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
                      <div class="col-3">
                        <label>Audit Target</label>
                      </div>
                      
                      <div class="col-3">
                        <label>No. Of Agent</label>
                      </div>

                      <div class="col-3">
                        <label>Audit per Agent</label>
                      </div>

                      <div class="col-3">
                        <label>Audit per Day</label>
                      </div>
                  </div>

                    <div class="row">
                        <div class="col-3">
                          <input type="text" class="form-control" id="audit-target" name="audit-target" value="" placeholder="Audit Target">
                          {{--  --}}
                        </div>
                        
                        <div class="col-3">
                          <input type="text" class="form-control" id="no-of-agent" value="" name="no-of-agent" placeholder="No. Of Agent">
                        </div>

                        <div class="col-3">
                          <input type="text" class="form-control" id="audit-per-agent" value="" placeholder="Audit per Agent">
                        </div>

                        <div class="col-3">
                          <input type="text" class="form-control" id="audit-per-day" value="" placeholder="Audit per Day">
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
                        <input type="text" class="form-control" id="mon" value="" placeholder="Month">
                      </div>

                      <div class="col-3">
                        <input type="text" class="form-control" id="day" value="" placeholder="Day">
                      </div>

                      <div class="col-3">
                        <input type="text" class="form-control" id="mtd" value="" placeholder="Month To Date">
                      </div>

                      <button type="submit" class="btn btn-primary">Generate</button>

                    </div>
                  </div>

                  {{-- {{dd($parameters)}} --}}
            </div>

          </div>

          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">
                  <table border=0 cellpadding=0 cellspacing=0 width=382 style='border-collapse:
                          collapse;table-layout:fixed;width:286pt'>
                          <col width=64 style='width:48pt'>
                          <col width=120 style='mso-width-source:userset;mso-width-alt:4266;width:90pt'>
                          <col width=102 style='mso-width-source:userset;mso-width-alt:3612;width:76pt'>
                          <col width=96 style='mso-width-source:userset;mso-width-alt:3413;width:72pt'>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 width=64 style='height:14.4pt;width:48pt'></td>
                          <td width=120 style='width:90pt'></td>
                          <td width=102 style='width:76pt'></td>
                          <td width=96 style='width:72pt'></td>
                          </tr>
                          <tr height=21 style='height:15.6pt'>
                          <td height=21 style='height:15.6pt'></td>
                          <td colspan=3 class=xl75 width=318 style='border-right:.5pt solid #BF8F00;
                          width:238pt'>Outcome Wise Sampling</td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl74 width=120 style='width:90pt'>CRITERIA</td>
                          <td class=xl74 width=102 style='border-left:none;width:76pt'>%</td>
                          <td class=xl74 width=96 style='border-left:none;width:72pt'>COUNT</td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl71>{{$parameters[0]->samplingCriteria}}</td>
                          <td class=xl72 style='border-left:none'><input type='text' value='{{$parameters[0]->sampling_value_in_percent}}' id='info-per' name='' size='1' disabled></td>
                          <td class=xl73 style='border-left:none'><input type='text' value='0' id='info-val' name='info-val' size='3' readonly></td>
                          </tr>
                          <tr height=37 style='height:27.6pt'>
                          <td height=37 style='height:27.6pt'></td>
                          <td class=xl70 width=120 style='border-top:none;width:90pt'>{{$parameters[1]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[1]->sampling_value_in_percent}}' id='sad-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='sad-val' name='sad-val' size='3' readonly></td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl69 style='border-top:none'>{{$parameters[2]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[2]->sampling_value_in_percent}}' id='comp-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='comp-val' name='comp-val' size='3' readonly></td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl69 style='border-top:none'>{{$parameters[3]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[3]->sampling_value_in_percent}}' id='bald-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='bald-val' name='bald-val' size='3' readonly></td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl69 style='border-top:none'>{{$parameters[4]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[4]->sampling_value_in_percent}}' id='cfl-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='cfl-val' name='cfl-val' size='3' readonly></td>
                          </tr>
                          <tr height=21 style='height:15.6pt'>
                          <td height=21 style='height:15.6pt'></td>
                          <td class=xl68></td>
                          <td class=xl65 style='border-top:none'>Total</td>
                          <td class=xl78 style='border-top:none;border-left:none'><input type='text' value='0' id='out-total' name='out-total' size='4' readonly></td>
                          </tr>
                          <![if supportMisalignedColumns]>
                          <tr height=0 style='display:none'>
                          <td width=64 style='width:48pt'></td>
                          <td width=120 style='width:90pt'></td>
                          <td width=102 style='width:76pt'></td>
                          <td width=96 style='width:72pt'></td>
                          </tr>
                          <![endif]>
                          </table>


                          <table border=0 cellpadding=0 cellspacing=0 width=382 style='border-collapse:
                          collapse;table-layout:fixed;width:286pt'>
                          <col width=64 style='width:48pt'>
                          <col width=120 style='mso-width-source:userset;mso-width-alt:4266;width:90pt'>
                          <col width=102 style='mso-width-source:userset;mso-width-alt:3612;width:76pt'>
                          <col width=96 style='mso-width-source:userset;mso-width-alt:3413;width:72pt'>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 width=64 style='height:14.4pt;width:48pt'></td>
                          <td width=120 style='width:90pt'></td>
                          <td width=102 style='width:76pt'></td>
                          <td width=96 style='width:72pt'></td>
                          </tr>
                          <tr height=21 style='height:15.6pt'>
                          <td height=21 style='height:15.6pt'></td>
                          <td colspan=3 class=xl75 width=318 style='border-right:.5pt solid #BF8F00;
                          width:238pt'>Duration Wise Sampling</td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl74 width=120 style='width:90pt'>CRITERIA</td>
                          <td class=xl74 width=102 style='border-left:none;width:76pt'>%</td>
                          <td class=xl74 width=96 style='border-left:none;width:72pt'>COUNT</td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl71>{{$parameters[5]->samplingCriteria}}</td>
                          <td class=xl72 style='border-left:none'><input type='text' value='{{$parameters[5]->sampling_value_in_percent}}' id='sc-per' name='' size='1' disabled></td>
                          <td class=xl73 style='border-left:none'><input type='text' value='0' id='sc-val' name='sc-val' size='3' readonly></td></td>
                          </tr>
                          <tr height=37 style='height:27.6pt'>
                          <td height=37 style='height:27.6pt'></td>
                          <td class=xl70 width=120 style='border-top:none;width:90pt'>{{$parameters[6]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[6]->sampling_value_in_percent}}' id='mc-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='mc-val' name='mc-val' size='3' readonly></td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl69 style='border-top:none'>{{$parameters[7]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[7]->sampling_value_in_percent}}' id='lc-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='lc-val' name='lc-val' size='3' readonly></td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl69 style='border-top:none'>{{$parameters[8]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[8]->sampling_value_in_percent}}' id='uc-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='uc-val' name='uc-val' size='3' readonly></td>
                          </tr>
                          <tr height=21 style='height:15.6pt'>
                          <td height=21 style='height:15.6pt'></td>
                          <td class=xl68></td>
                          <td class=xl65 style='border-top:none'>Total</td>
                          <td class=xl78 style='border-top:none;border-left:none'><input type='text' value='0' id='dur-total' name='dur-total' size='4' readonly></td>
                          </tr>
                          <![if supportMisalignedColumns]>
                          <tr height=0 style='display:none'>
                          <td width=64 style='width:48pt'></td>
                          <td width=120 style='width:90pt'></td>
                          <td width=102 style='width:76pt'></td>
                          <td width=96 style='width:72pt'></td>
                          </tr>
                          <![endif]>
                          </table>

                          <table border=0 cellpadding=0 cellspacing=0 width=382 style='border-collapse:
                          collapse;table-layout:fixed;width:286pt'>
                          <col width=64 style='width:48pt'>
                          <col width=120 style='mso-width-source:userset;mso-width-alt:4266;width:90pt'>
                          <col width=102 style='mso-width-source:userset;mso-width-alt:3612;width:76pt'>
                          <col width=96 style='mso-width-source:userset;mso-width-alt:3413;width:72pt'>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 width=64 style='height:14.4pt;width:48pt'></td>
                          <td width=120 style='width:90pt'></td>
                          <td width=102 style='width:76pt'></td>
                          <td width=96 style='width:72pt'></td>
                          </tr>
                          <tr height=21 style='height:15.6pt'>
                          <td height=21 style='height:15.6pt'></td>
                          <td colspan=3 class=xl75 width=318 style='border-right:.5pt solid #BF8F00;
                          width:238pt'>Segment Wise Sampling</td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl74 width=120 style='width:90pt'>CRITERIA</td>
                          <td class=xl74 width=102 style='border-left:none;width:76pt'>%</td>
                          <td class=xl74 width=96 style='border-left:none;width:72pt'>COUNT</td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl71 width=120 style='border-top:none;width:90pt'>{{$parameters[9]->samplingCriteria}}</td>
                          <td class=xl72 style='border-left:none'><input type='text' value='{{$parameters[9]->sampling_value_in_percent}}' id='pb-per' name='' size='1' disabled></td>
                          <td class=xl73 style='border-left:none'><input type='text' value='0' id='pb-val' name='pb-val' size='3' readonly></td>
                          </tr>
                          <tr height=37 style='height:27.6pt'>
                          <td height=37 style='height:27.6pt'></td>
                          <td class=xl70 width=120 style='border-top:none;width:90pt'>{{$parameters[10]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[10]->sampling_value_in_percent}}' id='gr-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='gr-val' name='gr-val' size='3' readonly></td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl69 style='border-top:none'>{{$parameters[11]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[11]->sampling_value_in_percent}}' id='sl-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='sl-val' name='sl-val' size='3' readonly></td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl69 style='border-top:none'>{{$parameters[12]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[12]->sampling_value_in_percent}}' id='bl-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='bl-val' name='bl-val' size='3' readonly></td>
                          </tr>
                          <tr height=19 style='height:14.4pt'>
                          <td height=19 style='height:14.4pt'></td>
                          <td class=xl69 style='border-top:none'>{{$parameters[13]->samplingCriteria}}</td>
                          <td class=xl66 style='border-top:none;border-left:none'><input type='text' value='{{$parameters[13]->sampling_value_in_percent}}' id='ot-per' name='' size='1' disabled></td>
                          <td class=xl67 style='border-top:none;border-left:none'><input type='text' value='0' id='ot-val' name='ot-val' size='3' readonly></td>
                          </tr>
                          <tr height=21 style='height:15.6pt'>
                          <td height=21 style='height:15.6pt'></td>
                          <td class=xl68></td>
                          <td class=xl65 style='border-top:none'>Total</td>
                          <td class=xl78 style='border-top:none;border-left:none'><input type='text' value='0' id='seg-total' name='seg-total' size='4' readonly></td>
                          </tr>
                          <![if supportMisalignedColumns]>
                          <tr height=0 style='display:none'>
                          <td width=64 style='width:48pt'></td>
                          <td width=120 style='width:90pt'></td>
                          <td width=102 style='width:76pt'></td>
                          <td width=96 style='width:72pt'></td>
                          </tr>
                          <![endif]>
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
                    <th>TOTAL(01 till Today)</th>
                    <th>AUDIT TARGET(%)</th>
                    <th>MONTHLY TARGET</th>
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
                    {{-- {{'n-'.$wc->wc}} --}}
                    <td><input type='text' class="form-control" name='{{'wc-'.$wc->wc}}' id='{{'mt-'.$wc->wc}}' value='' size='5' readonly/></td>
                    <td><input type='text' class="form-control" name='' id='{{'mtt-'.$wc->wc}}' value='' size='5' disabled /></td>
                    <td><input type='text' class="form-control" name='' id='' value='' size='5' disabled /></td>
                    <td><input type='text' class="form-control" name='' id='' value='' size='5' disabled /></td>
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
  

    //Code for sample calculation
    $('#audit-target').on('input',function(){
        //var constant = 300;
        var auditTarget = $(this).val();
        //console.log(auditTarget);
        //var val = ((($('#info-per').val()))/constant)*auditTarget;

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


        //Update Monthly Target input field
        // Selector for input fields to be updated
        var inputFieldsToUpdate = $("input[id^='mt-']");
        var inputFieldsToUpdateMTD = $("input[id^='mtt-']");

        var day = $('#day').val();
        var mtd = $('#mtd').val();

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