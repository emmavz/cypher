<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!-- custom scripts-->
@include('sweetalert::alert')
<script src="{{ $cdn?? asset('vendor/sweetalert/sweetalert.all.js')  }}"></script>
@routes
<script src="{{ mix('front/js/main.js') }}"></script>