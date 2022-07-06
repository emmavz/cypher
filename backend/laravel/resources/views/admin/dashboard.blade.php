@extends('admin.layouts.app')

@section('main_content')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>{{ __('Dashboard') }}</h1>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">

    <div class="col-sm-12">
      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">{{ __('Dashboard') }}</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="card-body">
          {{ __('Welcome '.Auth::user()->name.'!') }}
        </div>
        <!-- /.card-body -->
        <div class="card-footer">
          {{ __('Footer') }}
        </div>
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->
    </div>

  </section>
  <!-- /.content -->
@endsection