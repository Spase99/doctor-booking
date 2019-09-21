@role('admin')
    @include("backend/admin/index")
@endrole

@role('doctor')
    @include("backend/doctor/index")
@endrole

@role('reception')
    @include("backend/reception/index")
@endrole
