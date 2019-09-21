@extends('backend.layout')

@section('content')
    <div id="app">
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Patient buchen</h1>
            <reception-patient-calendar></reception-patient-calendar>
        </div>
    </div>
    <script>
        var permissions = {!! json_encode($permissions) !!};
        var user = {{ $user->id }};
    </script>
@endsection