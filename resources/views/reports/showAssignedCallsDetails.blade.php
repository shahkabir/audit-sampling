@extends('layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <form name="generateSample" action="{{ route('show-report-assigned-calls-details')}}" method="post">
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
                <!-- <h5 class="card-title">Card title</h5>
                <p class="card-text">
                  Some quick example text to build on the card title and make up the bulk of the card's
                  content.
                </p> -->

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
            
                 <div class="form-group">
                    <label>Assigned Date Range:</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                          </span>
                        </div>
                        <input type="text" class="form-control float-left .col-4" id="reservation" name="date_range">
                    </div>

                    <label>LOB:</label>
                    <div class="row">
                      <select class="form-control" name="lob">
                        <option value="">--select--</option>
                        <option value="IB">Inbound</option>
                        <option value="OB">Outbound</option>
                        <option value="SM">Social Media</option>
                      </select>
                    </div>

                    <label></label>
                    <div class="row">
                      <button type="submit" class="btn btn-primary">SHOW</button>
                    </div>
                  </div>
                  {{-- {{ dd($data) }} --}}
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
              @if(isset($data) && count($data) > 0)

               <table id="example1" class="table table-bordered table-striped">
                 <thead>
                 <tr>
                  @foreach ($headers as $h)
                  <th>{{ $h }}</th>
                   {{-- <th>ASSIGNED DATETIME</th>
                   <th>ASSIGNED BY</th>
                   <th>ASSIGNED TO</th>
                   <th>PARENT ID</th>
                   <th>CALLID</th>
                   <th>WORKCODE</th>
                   <th>OUTCOME</th>
                   <th>DURATION</th>
                   <th>SKILLNO</th>
                   <th>MSISDN</th>
                   <th>AUDIT STATUS</th>
                   <th>AUDIT DATE</th>
                   <th>IGNORE COMMENT</th> --}}      
                   @endforeach
                 </tr>
                 </thead>
                 <tbody>
                   @foreach ($data as $ss)
                 <tr>
                   {{-- {{dd($ss)}} --}}
                   <td>{{$ss->assignedDate}}</td>
                   <td>{{$ss->assignedBy}}</td>
                   <td>{{$ss->assignedTo}}</td>
                   <td>{{$ss->parentID }}</td>
                   {{-- assignedTo, parentID, CALLID,DURATION,SKILLNO,CODE,OUTCOME,MSISDN,
                   case auditStatus --}}
                   @if($lob == 'IB')
                   <td>{{str_pad($ss->CALLID, 20, '0', STR_PAD_LEFT);}}</td>
                   <td>{{$ss->DURATION}}</td>
                   <td>{{$ss->SKILLNO}}</td>
                   <td>{{$ss->CODE}}</td>
                   <td>{{$ss->OUTCOME}}</td>                  
                   <td>{{$ss->MSISDN}}</td>
                   @endif
                   
                   @if($lob == 'OB')
                   <td>{{$ss->SOID}}</td>
                   <td>{{'#'.$ss->CALLID_OB}}</td>
                   <td>{{$ss->MSISDN}}</td>
                   <td>{{$ss->STATUS}}</td>
                   <td>{{$ss->SOURCE}}</td>
                   <td>{{$ss->CATEGORY}}</td>
                   <td>{{$ss->SUBCATEGORY}}</td>
                   @endif

                   <td>{{$ss->auditStatus}}</td>
                   <td>{{$ss->auditDate}}</td>
                   <td>{{$ss->ignoredComment}}</td>
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
               @else
                {{-- {{'NO DATA FOUND'}} --}}
              @endif 
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

      buttons: ["copy", "csv", "excel", "pdf"]
   }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


   // $('#example1').DataTable({
   
   //   "lengthChange": false,
   //   "searching": false,
   
   
   //   "autoWidth": false,
   //   "responsive": true,
   // });
   
 });

 //Date range picker
 $('#reservation').daterangepicker();

</script>
@endsection