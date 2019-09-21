@extends('frontend.layout')

@section('content')
    @if($hasAppointmentsConfigured)
        
        <h1 class="current-booking-doc-heading">{{ $doctor->name }}</h1>
        <hr class="heading-hr">
        <div id="app">
            <patient-calendar
                :doctor="{!! htmlentities(json_encode($doctor, JSON_HEX_QUOT), ENT_QUOTES) !!}"
                no-turnin-message="{!! htmlentities($noTurninMessage, ENT_QUOTES) !!}"
                :pay-types="{!! htmlentities($payTypes, ENT_QUOTES) !!}"></patient-calendar>
        </div>
    @else
        <p>Dieser Arzt/Diese Ärztin hat Online-Buchungen leider noch nicht konfiguriert.</p>
    @endif
    <script>
        var user = null;
        var permissions = [];
    </script>
@endsection
