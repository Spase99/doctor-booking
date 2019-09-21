@extends('backend.layout')

@section('content')
    <div id="app">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Überziehung notieren</h1>
            <booking-calendar></booking-calendar>
        </div>
    </div>
    <script>
        var appointmentTypes = [];
        var selectableDoctors = {!! $selectableDoctors !!};
        var permissions = {!! json_encode($permissions) !!};
        var user = {{ $user->id }};
    </script>
@endsection