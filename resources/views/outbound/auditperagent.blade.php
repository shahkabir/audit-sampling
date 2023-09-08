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
   @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
  <!-- Main content -->
  <section class="content">
    <!-- Default box -->
    <div class="card">
        <div class="card-header">
          <h3 class="card-title">Update Audit Per Agent</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped projects">
              <thead>
                  <tr>
                    <th>
                        SL
                    </th>
                     
                      <th style="width: 20%">
                          Current Value
                      </th>
                      <th style="width: 30%">
                          Updated By
                      </th>
                      <th>
                        Updated Date
                      </th>
                      
                      <th style="width: 20%">
                      </th>
                  </tr>
              </thead>
              <tbody>
                @php
                $i=1;
                @endphp
                {{-- {{dd($parameters)}} --}}
                @foreach ($parameters as $data)
                  <tr>
                    <td>
                        {{$i}}
                        @php
                        $i++;
                        @endphp
                      </td>

                      <td>
                        {{ $data->value }}
                      </td>
                      <td>
                        {{ $data->updatedBy }}
                      </td>
                      <td class="project_progress">
                        {{ $data->updatedDate }}
                      </td>
                      
                      <td class="project-actions text-right">
                          
                        <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" value="{{$data->tableID}}">
                          <i class="fas fa-pencil-alt"> </i>UPDATE
                        </button>

                          {{-- <a class="btn btn-info btn-sm" href="update-audit-per-agent/{{$data->tableID}}">
                              <i class="fas fa-pencil-alt"></i>
                              EDIT
                          </a> --}}
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
  </div>
  <!-- /.content-wrapper -->

  <div class="modal fade" id="modal-lg">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Audit Per Agent</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

       <form action="{{ url('update-audit-per-agent') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-body">

          <div class="form-group mb-3">
            <label for="">CURRENT VALUE</label>
            <input type="text" name="currentval" id="currentval" class="form-control" readonly/>
          </div>

          <div class="form-group mb-3">
            <label for="">NEW VALUE</label>
            <input type="text" name="newval" id="newval" class="form-control" required/>
            <input type="hidden" name="tableID" id="tableID" class="form-control" readonly/>
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

  <!-- page specific script -->
  <script>
      $(document).ready(function(){

          $(document).on('click','.btn-dark',function(){
              var id = $(this).val();
              //alert(id);

              $("#modal-lg").modal('show');

              $.ajax({
                  type: "GET",
                  url: "/audit-per-agent-get/"+id,
                  success: function (response){
                      console.log(response);

                      $('#currentval').val(response[0].value);
                      $('#tableID').val(response[0].tableID);
                      // $('#mincurrent').val(response[0].min);
                      // $('#maxcurrent').val(response[0].max);
                      
                      //console.log(response[0].target_per);
                      // var target = response.results[0].target_per;
                      // if(target == null)
                      //     target = '';
                      
                      // $('#current').val(target);
                  }
              });
          });
    })
  </script>
  @endsection