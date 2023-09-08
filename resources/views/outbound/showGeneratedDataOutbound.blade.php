@extends('layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   {{-- <div class="content-header">
     <div class="container-fluid">
       <div class="row mb-2">
         <div class="col-sm-6">
           <!-- <h1 class="m-0">Starter Page</h1> -->
         </div><!-- /.col -->
         <div class="col-sm-6">
           <ol class="breadcrumb float-sm-right">
             <li class="breadcrumb-item"><a href="#">Home</a></li>
             <li class="breadcrumb-item active">Starter Page</li>
           </ol>
         </div><!-- /.col -->
       </div><!-- /.row -->
     </div><!-- /.container-fluid -->
   </div> --}}
   <!-- /.content-header -->

   <form name="generateSample" action="{{URL::to('assign-to-users-outbound')}}" method="post" id="generateSample">
     @csrf
   <!-- Main content -->
   <div class="content">
     <div class="container">
       {{-- container-fluid --}}
       <div class="row">
         {{-- </div> --}}

         <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
               
                        @if (isset($notification))
                            <div class="alert alert-info">
                                {{ $notification }}
                            </div>
                        @endif

                        @if (isset($checkTodayMsg))
                            <div class="alert alert-warning">
                                {{ $checkTodayMsg }}
                            </div>
                        @endif    
                          <div class="row">
                            <div class="col-12">
                              <label>Available Agents</label>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-12">

                            <table>
                                <tr>
                                    <td></td>
                                    <td>Agent</td>
                                    <td>Calls to be assigned</td>
                                </tr>

                                
                                @foreach ($users as $user)
                                <tr>
                                    <td><input type="checkbox" name="users[]" value="{{ $user->userid }}" class="user-checkbox" checked>  </td>
                                    <td>{{ $user->userid }}</td>
                                    <td><input type='text' class="form-control user-value" value="" id='' name="user_values[{{$user->userid}}]" data-userid="{{$user->userid}}"></td>
                                </tr>
                                @endforeach
                                
                            </table>

                            {{-- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; --}}
                             
                            </div>
                          </div>

                          <h1></h1>

                          {{-- <div class="row">
                            <div class="col-12">
                              <label>Set Fixed Calls</label>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-3">
                              <input type='text' class="form-control" value='' id='fixedCalls' name='fixedCalls'>
                            </div>
                          </div> --}}

                          <h1></h1>

                          <div class="col-3">
                            <div class="custom-control custom-switch">
                              <input type="checkbox" name="isRandom" id="isRandom" class="custom-control-input"/>
                              <label class="custom-control-label" for="isRandom">Assign Randomly</label>
                            </div>
                          </div>

                          <div class="form-group">
                              <div class="row">
                                

                                <button type="submit" class="btn btn-primary">ASSIGN TO USERS</button>
                                {{-- <input type="hidden" value="{{$sampleHistoryID}}" name="sampleHistoryID"> --}}
                              </div>
                          </div>
                  {{-- {{ dd($selectedSample) }} --}}
            </div>
          </div>
         </div>
         

         {{-- @if (Route::currentRouteName() === 'generateSampling') --}}
         <div class="col-lg-12">
           <div class="card">
             {{-- <div class="card-header">
               <h3 class="card-title">DataTable with default features</h3>
             </div>
             <!-- /.card-header --> --}}
             <div class="card-body">

               <table id="example1" class="table table-bordered table-striped">
                 <thead>
                 <tr>
                   <th>TABLEID</th>
                   <th>PARENTID</th>
                   <th>SO DATE</th>
                   <th>SO TICKET</th>
                   <th>MSISDN</th>
                   <th>STATUS</th>
                   <th>CHANNEL(SOURCE)</th>
                   <th>CATEGORY</th>
                   <th>SUB CATEGORY</th>
                   <th>SO OPENDATE</th>
                   <th>ALTERNATE CONTACT</th>
                 </tr>
                 </thead>
                 <tbody>
                   @foreach ($selectedSample as $ss)
                 <tr>
                   <td>{{$ss->tableID}}</td>
                   <td>{{$ss->parentID}}</td>
                   <td>{{$ss->reportdate}}</td>
                   <td>{{$ss->ID}}</td>
                   <td>{{$ss->MSISDN}}</td>
                   <td>{{$ss->STATUS}}</td>
                   <td>{{$ss->SOURCE}}</td>
                   <td>{{$ss->CATEGORY}}</td>
                   <td>{{$ss->SUBCATEGORY }}</td>
                   <td>{{$ss->OPENDATE }}</td>
                   <td>{{$ss->ALTERNATECONTACT }}</td>
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
         {{-- @endif --}}
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

      buttons: ["copy", "csv", "excel", "pdf"]
   }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


   // $('#example1').DataTable({
   
   //   "lengthChange": false,
   //   "searching": false,
   
   
   //   "autoWidth": false,
   //   "responsive": true,
   // });
  
  $('#generateSample').submit(function(event){
    
      var isRandomChecked = $('#isRandom').is(':checked');
      if(!isRandomChecked) 
      {
        var checked = false;
        var valid = true;

        $('.user-checkbox').each(function(){

          if ($(this).is(':checked')) {
              
              checked = true;

              var checkbox = $(this);
              var userId = checkbox.val();
              var inputValue = $('.user-value[data-userid="' + userId + '"]').val();

              console.log('checked:'+checked);
              console.log('userId:'+userId);
              console.log('inputValue:'+inputValue);

              if ((!inputValue || parseInt(inputValue) <= 0)) {
                  alert('Please provide a positive value for Agent: ' + userId + '.');
                  valid = false;
                  return false; // Exit the loop
              }
            }
        });

        if (!checked)
        {
            alert('Please select at least one Agent.');
            event.preventDefault();
        } else if (!valid) {
            event.preventDefault();
        }
    
        //event.preventDefault();
    }

    //event.preventDefault();

  });  

 });
</script>
@endsection