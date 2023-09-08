@extends('layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <form name="generateSample" action="{{ route('show-source-data')}}" method="post">
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
                    <label>Generated Date Range:</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                          </span>
                        </div>
                        <input type="text" class="form-control float-left .col-4" id="reservation" name="date_range">
                        </div>

                  <label></label>
                    <div class="row">
                      <button type="submit" class="btn btn-primary">SHOW</button>
                    </div>
                  </div>
                  {{-- {{dd($parameters)}} --}}
            </div>
          </div>
         </div>
         

         {{-- @if (Route::currentRouteName() === 'generateSampling') --}}
         @if(isset($data) && count($data) > 0)
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
                   <th>SOURCE DATA DATE</th>
                   <th>SOURCE NAME</th>
                   <th>FILENAME</th>
                   <th>COUNT</th>
                   <th>UPLOAD DATETIME</th>                                  
                 </tr>
                 </thead>
                 <tbody>
                   @foreach ($data as $ss)
                 <tr>
                   <td>{{$ss->data_date}}</td>
                   <td>{{$ss->source}}</td>
                   <td>{{$ss->filename}}</td>
                   <td>{{$ss->count}}</td>
                   <td>{{$ss->upload_datetime}}</td>
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
         @endif
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