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

<!-- Main content -->
<section class="content">
<!-- Horizontal Form -->
<div class="card card-info">
    <div class="card-header">
      <h3 class="card-title">Update Sampling Criteria</h3>
    </div>
    <!-- /.card-header -->
    <!-- form start -->
    <form class="form-horizontal" action="{{URL::to('process-sampling/'.$data->Id)}}" method="post">
        @csrf
      <div class="card-body">
        {{-- <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">CRITERIA TYPE</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="inputEmail3" value="">
            </div>
          </div> --}}
          <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">SAMPLING CRITERIA</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="inputEmail3" value="{{$data->samplingCriteria}}" disabled>
            </div>
          </div>
        <div class="form-group row">
          <label for="inputEmail3" class="col-sm-2 col-form-label">CURRENT VALUE</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="inputEmail3" value="{{$data->sampling_value_in_percent}}" disabled>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputPassword3" class="col-sm-2 col-form-label">NEW VALUE</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="inputPassword3" name="new_sampling_value">
          </div>
        </div>
        {{-- <div class="form-group row">
          <div class="offset-sm-2 col-sm-10">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="exampleCheck2">
              <label class="form-check-label" for="exampleCheck2">Remember me</label>
            </div>
          </div>
        </div> --}}
      </div>
      <!-- /.card-body -->
      <div class="card-footer">
        {{-- <button type="submit" class="btn btn-info">Sign in</button> --}}
        <button type="submit" class="btn btn-default float-right">SUBMIT</button>
      </div>
      <!-- /.card-footer -->
    </form>
  </div>
</section>
</div>
@endsection