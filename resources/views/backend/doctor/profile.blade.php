@extends('backend.layout')

@section('content')
    <div id="app">
        <div class="container-fluid">
            <h1 class="h3 mb-0 text-gray-800 mb-4">Profil bearbeiten</h1>
            <div class="row">
                <div class="col-12 col-md-9">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form action="{{ route('doctor.updateProfile') }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('PUT') }}

                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input class="form-control" name="name" id="name" value="{{ $doc->name }}" type="text" />
                                </div>
                                <div class="form-group">
                                    <label for="password">Passwort</label>
                                    <input class="form-control" name="password" id="password" type="password" />
                                </div>
                                <div class="form-group">
                                    <label for="email">E-Mail</label>
                                    <input class="form-control" name="email" id="email" value="{{ $doc->email }}" type="text" />
                                </div>
                                <div class="form-group">
                                    <label for="phone">Telefon</label>
                                    <input class="form-control" name="phone" id="phone" value="{{ $doc->phone }}" type="text" />
                                </div>
                                <button type="submit" class="btn btn-primary">Speichern</button>
                            
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection