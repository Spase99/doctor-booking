@extends('backend.layout')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Administratoransicht</h1>     

    <div id="app">
        <div class="row" id="_rooms">
            <div class="col-12">
                <room-component></room-component>
            </div>
        </div>

        <div class="row" id="_docs">
            <div class="col-12">
                <doctor-component></doctor-component>
            </div>
        </div>

        <div class="row" id="_types">
            <div class="col-12">
                <type-component></type-component>
            </div>
        </div>

        <div class="row" id="_blockings">
            <div class="col-12">
                <openings-component></openings-component>
            </div>
        </div>
    </div>
</div>

<script>
    var permissions = {!! json_encode($permissions) !!};
    var user = {{ $user->id }};
</script>

@endsection
