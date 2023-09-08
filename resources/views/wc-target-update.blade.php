@extends('layouts.app')
@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    {{-- <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Projects</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Projects</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section> --}}
  <form name="saveWC" action="{{URL::to('save-wc')}}" method="post">
    @csrf
    @method('PUT')
  <!-- Main content -->
  <section class="content">
    <!-- Default box -->

    <div class="container">
      {{-- container-fluid --}}
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">         
                  <div class="row">
                    {{-- <div class="col-3">
                      <label>Total AUDIT TARGET(%)</label>
                    </div>

                    <div class="col-3">
                      <label>Total CUSTOM TARGET(%)</label>
                    </div> --}}

                    <div class="col-3">
                      <label>Current Total Custom %</label>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-3">
                    </div>

                    <div class="col-3">
                    </div>

                    <div class="col-3">
                      {{-- <label>Total CUSTOM TARGET(%)</label> --}}
                    </div>

                  </div>

                  <div class="row">
                    {{-- <div class="col-3">
                      <input type="text" class="form-control" id="tot-audit-target" name="tot-audit-target" value="" readonly>
                    </div>

                    <div class="col-3">
                      <input type="text" class="form-control" id="tot-custom-target" name="tot-custom-target" value="" readonly>
                    </div> --}}

                      <div class="col-3">
                        <input type="text" class="form-control" id="tot-custom" name="tot-custom" value="" readonly>
                        {{-- {{$total[0]->total}} --}}
                      </div>
                  </div>
                  <h1></h1>
                  <h1></h1>
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
                        <label class="custom-control-label" for="setfromAudit">Set CUSTOM TARGET=blank</label>
                      </div>
                    </div>

                    <div class="col-3">
                      <button type="submit" class="btn btn-primary">SAVE CURRENT VALUES</button>
                    </div>

                    {{-- <div class="col-3">
                      <input type="text" class="form-control" id="totalCustom" value="" readonly>
                    </div> --}}
                  </div>

            </div>
          </div>
        </div>
      </div>
    </div>
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
          <h3 class="card-title">WORKCODE CUSTOM TARGET</h3>

          {{-- <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div> --}}
          
        </div>
        <div class="card-body p-0">
          <table id="example1" class="table table-bordered table-striped">
              <thead>
                  <tr>
                    <th>
                        SL
                    </th>
                     
                      <th style="width: 20%">
                          WORKCODE
                      </th>
                      <th style="width: 30%">
                          QUERY
                      </th>
                      <th style="width: 10%">
                          TOTAL(01 till Yesterday)
                      </th>
                      <th style="width: 10%">
                          AUDIT TARGET(%)
                      </th>

                      <th>
                          CUSTOM TARGET(%)
                      </th>
                      
                      <th style="width: 20%">
                      </th>
                  </tr>
              </thead>
              <tbody>
                @php
                
                //dd($total);

                $i=1;
                @endphp
                
                @foreach ($parameters as $data)
                  <tr>
                    <td>
                        {{$i}}
                        @php
                        $i++;
                        @endphp
                      </td>

                      <td>
                        {{ $data->cwc }}
                      </td>
                      <td>
                        {{ $data->cwcnames }}
                      </td>

                      <td>
                        {{ $data->count_till_date }}
                      </td>

                      <td>
                        {{ $data->audit_target_in_per }}
                        <input type='hidden' class="form-control" name='' id='{{'atp-'.$data->cwc}}' value='{{ $data->audit_target_in_per }}' size='5' readonly />
                      </td>

                      <td>
                        <input type='text' class="form-control" name='{{'catp-'.$data->cwc}}' id='{{'catp-'.$data->cwc}}' value="{{ $data->target_per }}" size='4'/>
                      </td>
                      
                      <td class="project-actions">
                        {{-- text-right --}}
                          {{-- <a class="btn btn-primary btn-sm" href="#">
                              <i class="fas fa-folder">
                              </i>
                              View
                          </a> --}}
                          {{-- <a class="btn btn-info btn-sm" href="update-sampling/{{$data->cwc}}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                          </a> --}}

                          <button type="button" class="btn btn-info" data-toggle="modal" value="{{$data->cwc}}">
                            <i class="fas fa-pencil-alt"> </i>Edit
                          </button>
                          {{-- data-target="#modal-xl" --}}

                          {{-- <a class="btn btn-danger btn-sm" href="#">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                          </a> --}}
                      </td>
                  </tr>
                  @endforeach
                  
                 
              </tbody>
          </table>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </form>
  </div>
  <!-- /.content-wrapper -->


  <div class="modal fade" id="modal-lg">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Set Workcode Custom Target</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

       <form action="{{ url('update-wc') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-body">

          <div class="form-group mb-3">
            <label for="">WORKCODE</label>
            <input type="text" name="wc" id="wc" class="form-control" readonly/>
          </div>
          
          <div class="form-group mb-3">
            <label for="">QUERY</label>
            <input type="text" name="query" id="query" class="form-control" readonly/>
          </div>

          <div class="form-group mb-3">
            <label for="">CURRENT VALUE(%) </label>
            <input type="text" name="current" id="current" class="form-control" readonly/>
          </div>

          <div class="form-group mb-3">
            <label for="">NEW VALUE(%)</label>
            <input type="text" name="new" id="new" class="form-control"/>
            {{-- required --}}
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
       </form>

      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

<!-- Page specific script -->
<script>

$(function () {

// // Initialize the DataTable
// var table = $('#example1').DataTable();

// // Add export buttons
// new $.fn.dataTable.Buttons(table, {
//   buttons: [
//     {
//       extend: 'csv',
//       text: 'Export CSV',
//       action: function(e, dt, button, config) {
//         // Add custom logic to include input field values in exported data
//         var exportData = [];
//         table.rows().every(function(rowIdx, tableLoop, rowLoop) {
//           var rowData = table.row(rowIdx).data();
//           var inputValues = [];

//           // Get the input field values for each row
//           $(this.node()).find('input').each(function() {
//             inputValues.push($(this).val());
//           });

//           // Combine the input field values with the existing row data
//           var mergedData = rowData.slice(0, -1).concat(inputValues);
//           exportData.push(mergedData);
//         });

//         // Create a temporary table to hold the export data
//         var tempTable = $('<table></table>').DataTable();
//         tempTable.rows.add(exportData).draw(false);

//         // Trigger the CSV export using the temporary table
//         tempTable.buttons.exportData({ format: 'csv' });
//       }
//     }
//   ]
// }).container().appendTo($('#buttons'));

// // Apply DataTables options and initialization
// table.buttons().container().appendTo($('#buttons'));


      $("#example1").DataTable({
        pageLength: -1,
        scrollX: true,
        scrollCollapse: true,
        order: [[4, 'desc']],
        //"responsive": true, 
        // "lengthChange": false, 
        autoWidth: true,
        paging: true,
        // "ordering": false,
        //"info": false,
  
        //"order": false,
        bLengthChange: false,
        scrollY: "1200px",
  
        columnDefs: [
              { "width": "12px", "targets": "1" }
          ],
  
      //"bAutoWidth": false,
  
        "buttons": ["copy", "csv", "excel", "pdf"]
      }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });


    $(document).ready(function(){

        $(document).on('click','.btn-info',function(){
            var id = $(this).val();
            //alert(id);

            $("#modal-lg").modal('show');

            $.ajax({
                type: "GET",
                url: "/get-wc/"+id,
                success: function (response){
                    //console.log(response.results[0].cwc);

                    $('#wc').val(response.results[0].cwc);
                    $('#query').val(response.results[0].cwcnames);
                    
                    //console.log(response[0].target_per);
                    var target = response.results[0].target_per;
                    if(target == null)
                        target = '';
                    
                    $('#current').val(target);
                }
            });
        });


            //Custom Target
            // Calculate sum on page load
            calculateSum();

            // Listen for changes in input fields
            $('input[id^="atp-"], input[id^="catp-"]').on('input', function() {
              calculateSum();
            });

            function calculateSum() {
              var sum = 0;

              $('input[id^="atp-"]').each(function() { //input[id^="catp-"]
                var auditTarget = parseFloat($(this).val());
                var customTarget = parseFloat($(this).closest('tr').find('input[id^="catp-"]').val());

                // console.log('auditTarget:'+auditTarget);
                // console.log('customTarget:'+customTarget);

                  if (!isNaN(customTarget)) {
                    sum += customTarget;
                  } else if (!isNaN(auditTarget)) {
                    sum += auditTarget;
                  }
              });

              //console.log('SUM:'+sum);
              $('#tot-custom').val(sum);


              var auditSum = 0;
              var customSum = 0;

              $('input[id^="atp-"]').each(function() {
                var auditTarget = parseFloat($(this).val());
                if (!isNaN(auditTarget)) {
                  auditSum += auditTarget;
                }
              });

              $('input[id^="catp-"]').each(function() {
                var customTarget = parseFloat($(this).val());
                if (!isNaN(customTarget)) {
                  customSum += customTarget;
                  //console.log(customTarget);
                }
              });

              $('#tot-audit-target').val(auditSum);
              $('#tot-custom-target').val(customSum);
            }



      //Set Custom Target to 0
      $("#setZero").on("change", function() {
        if ($(this).is(":checked")) {
          $("input[id^='catp-']").val(0);
          $('#tot-custom').val(0);

          //$('#audit-target').trigger('input');
          
        }
      });

      //Set Custom Target to blank
      $("#setfromAudit").on("change", function() {
        if ($(this).is(":checked")) {
          $("input[id^='catp-']").val('');
          $('#tot-custom').val('');

          //$('#audit-target').trigger('input');
          
        }
      });

    })
</script>
@endsection