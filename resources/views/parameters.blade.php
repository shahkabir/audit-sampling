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
          <h3 class="card-title">Update Sampling Criteria</h3>

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
                          Criteria Type
                      </th>
                      <th style="width: 30%">
                          Sampling Criteria
                      </th>
                      <th>
                          Value(%)
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
                        {{ $data->criteriaType }}
                      </td>
                      <td>
                        {{ $data->samplingCriteria }}
                      </td>
                      <td class="project_progress">
                        {{ $data->sampling_value_in_percent }}
                      </td>
                      
                      <td class="project-actions text-right">
                          

                          
                            @if($data->criteriaType == 'DURATION WISE')
                            <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" value="{{$data->samplingID}}">
                              <i class="fas fa-pencil-alt"> </i>UPDATE RANGE
                            </button>
                            @endif

                            
                          
                          <a class="btn btn-info btn-sm" href="update-sampling/{{$data->samplingID}}">
                              <i class="fas fa-pencil-alt"></i>
                              EDIT
                          </a>
                          {{-- <a class="btn btn-danger btn-sm" href="#">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                          </a> --}}
                      </td>
                  </tr>
                  @endforeach
                  
                  {{-- <tr>
                      
                      <td>
                          <a>
                              AdminLTE v3
                          </a>
                          <br/>
                          <small>
                              Created 01.01.2019
                          </small>
                      </td>
                      <td>
                          <ul class="list-inline">
                              <li class="list-inline-item">
                                  <img alt="Avatar" class="table-avatar" src="../../dist/img/avatar.png">
                              </li>
                              <li class="list-inline-item">
                                  <img alt="Avatar" class="table-avatar" src="../../dist/img/avatar3.png">
                              </li>
                              <li class="list-inline-item">
                                  <img alt="Avatar" class="table-avatar" src="../../dist/img/avatar4.png">
                              </li>
                              <li class="list-inline-item">
                                  <img alt="Avatar" class="table-avatar" src="../../dist/img/avatar5.png">
                              </li>
                          </ul>
                      </td>
                      <td class="project_progress">
                          <div class="progress progress-sm">
                              <div class="progress-bar bg-green" role="progressbar" aria-valuenow="77" aria-valuemin="0" aria-valuemax="100" style="width: 77%">
                              </div>
                          </div>
                          <small>
                              77% Complete
                          </small>
                      </td>
                     
                      <td class="project-actions text-right">
                          <a class="btn btn-primary btn-sm" href="#">
                              <i class="fas fa-folder">
                              </i>
                              View
                          </a>
                          <a class="btn btn-info btn-sm" href="#">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                          </a>
                          <a class="btn btn-danger btn-sm" href="#">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                          </a>
                      </td>
                  </tr> --}}
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
          <h4 class="modal-title">UPDATE RANGE</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

       <form action="{{ url('update-parameters') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-body">

          <div class="form-group mb-3">
            <label for="">Sampling Criteria</label>
            <input type="text" name="wc" id="wc" class="form-control" readonly/>
            <input type="hidden" name="samplingCriteriaID" id="samplingCriteriaID" class="form-control" readonly/>
          </div>
          
          {{-- <div class="form-group mb-3">
            <label for="">QUERY</label>
            <input type="text" name="query" id="query" class="form-control" readonly/>
          </div> --}}
          <table style="">
            <tr>
              <td></td>
              <td style="text-align: center"><label for="">MIN</label></td>
              <td></td>
              <td style="text-align: center"><label for="">MAX</label></td>
            </tr>
            <tr>
              <div class="form-group mb-3">
                <td><label for="">CURRENT VALUE(sec):</label></td>
                <td><input type="text" name="mincurrent" id="mincurrent" class="form-control" readonly/></td>
                <td>&lt;d&le;</td>
                <td><input type="text" name="maxcurrent" id="maxcurrent" class="form-control" readonly/></td>
              </div>
            </tr>
            <tr>
              <div class="form-group mb-3">
                <td><label for="">NEW VALUE(sec):</label></td>
                <td><input type="text" name="minnew" id="minnew" class="form-control" required/></td>
                <td>&lt;d&le;</td>
                <td><input type="text" name="maxnew" id="maxnew" class="form-control" required/></td>
              </div>
            </tr>
          </table>
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
                  url: "/parameter-get/"+id,
                  success: function (response){
                      //console.log(response);

                      $('#wc').val(response[0].criteria);
                      $('#samplingCriteriaID').val(response[0].samplingCriteriaID);
                      $('#mincurrent').val(response[0].min);
                      $('#maxcurrent').val(response[0].max);
                      
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