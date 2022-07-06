<!DOCTYPE html>
<html>
<head>
  @include('admin.layouts.head')
</head>
<body class="hold-transition sidebar-mini {{ getDirection() }}">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  @include('admin.layouts.header')
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  @include('admin.layouts.sidebar')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    @section('main_content')
    @show
  </div>
  <!-- /.content-wrapper -->

  @include('admin.layouts.footer')

  <!-- Control Sidebar -->
  @include('admin.layouts.controlsidebar')
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!-- jQuery Sortable -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('admin_assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('admin_assets/dist/js/adminlte.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('admin_assets/dist/js/demo.js') }}"></script>
<!-- DataTables -->
<script src="{{ asset('admin_assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin_assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admin_assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('admin_assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
  $.extend( true, $.fn.dataTable.defaults, {
      "language": {
        "url": '{{ asset('admin_assets/dist/lang/'.getLocale().'.json') }}'
      }
  });
</script>
<!-- Select2 -->
<script src="{{ asset('admin_assets/plugins/select2/js/select2.full.min.js') }}"></script>
@include('sweetalert::alert')
<script src="{{ $cdn?? asset('vendor/sweetalert/sweetalert.all.js')  }}"></script>
<!-- bs-custom-file-input -->
<script src="{{ asset('admin_assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script src="{{ asset('admin_assets/plugins/moment/moment.min.js') }}"></script>
<!-- flatpickr daterange picker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.3/flatpickr.min.js"></script>
<!-- include summernote css/js -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<!-- include summernote-ar-AR -->
<script src="{{ asset('admin_assets/plugins/summernote/lang/summernote-ar.js') }}"></script>
<!-- Fancy Box -->
<script src="{{ asset('admin_assets/plugins/jquery.fancybox.min.js') }}"></script>
@routes
<!-- Parsley -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js" integrity="sha512-eyHL1atYNycXNXZMDndxrDhNAegH2BDWt1TmkXJPoGf1WLlNYt08CSjkqF5lnCRmdm3IrkHid8s2jOUY4NIZVQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
  var lang = '{{ getLocale() }}';
</script>

<!-- Main Js -->
<script src="{{ mix('admin_assets/dist/js/main.js') }}"></script>
<!-- Functions-->
{{-- <script src="{{ asset('admin_assets/dist/js/admin-functions.js') }}"></script> --}}
@stack('script')
@yield('script')
</body>
</html>