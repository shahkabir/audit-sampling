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
         <div class="col-sm-6">
           <ol class="breadcrumb float-sm-right">
             <li class="breadcrumb-item"><a href="#">Home</a></li>
             <li class="breadcrumb-item active">Starter Page</li>
           </ol>
         </div><!-- /.col -->
       </div><!-- /.row -->
     </div><!-- /.container-fluid -->
   </div>
   <!-- /.content-header -->

   <form name="generateSample" action="{{URL::to('assign-to-users')}}" method="post">
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
                 <div class="form-group">
                  <label></label>
                    <div class="row">
                      <button type="submit" class="btn btn-primary">ASSIGN TO USERS</button>
                    </div>
                  </div>
                  {{-- {{dd($parameters)}} --}}
            </div>
          </div>
         </div>

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
                   <th>ID</th>
                   <th>CALL DATE</th>
                   <th>CALLID</th>
                   <th>MSISDN</th>
                   <th>DURATION</th>
                   <th>CODE</th>
                   <th>CATEGORY</th>
                   <th>QUERY</th>
                   <th>OUTCOME</th>
                   <th>UCID_CONNECT</th>
                   <th>SKILLNO</th>
                   <th>TALKTIME</th>
                   <th>WRAPUPCODE</th>
                   <th>AGENTNAME</th>                  
                 </tr>
                 </thead>
                 <tbody>
                   @foreach ($selectedSample as $ss)
                 <tr>
                   <td>{{$ss->parentID}}</td>
                   <td>{{$ss->ID}}</td>
                   <td>{{ $ss->{'START TIME'} }}</td>
                   <td>{{$ss->CALLID}}</td>
                   <td>{{$ss->MSISDN}}</td>
                   <td>{{$ss->DURATION}}</td>
                   <td>{{$ss->CODE}}</td>
                   <td>{{$ss->CATEGORY}}</td>
                   <td>{{$ss->QUERY}}</td>
                   <td>{{$ss->OUTCOME}}</td>
                   <td>{{$ss->UCID_CONNECT}}</td>
                   <td>{{$ss->SKILLNO}}</td>
                   <td>{{$ss->TALKTIME}}</td>
                   <td>{{$ss->WRAPUPCODE}}</td>
                   <td>{{$ss->AGENTNAME}}</td>
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

      buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
   });//.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


   // $('#example1').DataTable({
   
   //   "lengthChange": false,
   //   "searching": false,
   
   
   //   "autoWidth": false,
   //   "responsive": true,
   // });
   
 });
</script>
@endsection