@extends('admin.layouts.app')

@section('main_content')

  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>{{ __('Profile') }}</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="btn btn-block btn-info">{{ __('Dashboard') }}</a></li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <section class="content">
    <div class="col-sm-12">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">{{ __('Fill all the required fields') }} <span>*</span></h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form role="form" action="javascript:void(0)" method="POST" class="profile-update-form">
          @csrf
          @method('PUT')
          <div class="card-body">

            <div class="form-group">
              <label for="name">{{ __('Name') }} <span class="req">*</span></label>
              <input type="text" class="form-control" name="name" id="name" value="{{ auth()->user()->name }}">
            </div>

            <div class="form-group">
              <label for="email">{{ __('Email') }} <span class="req">*</span></label>
              <input type="email" class="form-control" name="email" id="email" value="{{ auth()->user()->email }}">
            </div>

            <div class="form-group">
              <label for="password">{{ __('Password') }} </label>
              <input type="password" class="form-control" name="password" id="password" >
            </div>

            <div class="form-group">
              <label for="password_confirmation">{{ __('Confirm Password') }} </label>
              <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" >
            </div>

            <div class="js-errors-container"></div>
          </div>
          <!-- /.card-body -->

          <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-progress">{{ __('Submit') }}</button>
          </div>
        </form>
      </div>
    </div>
  </section>

@endsection