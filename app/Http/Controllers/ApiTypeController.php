<?php

namespace App\Http\Controllers;

use App\Type;
use Illuminate\Http\Request;

class ApiTypeController extends Controller
{
    public function add(Request $request) {
        $type = new Type();
        $type->fill($request->all());
        $type->save();

        return response()->json($type);
    }

    public function list(Request $request) {
        $types = Type::all();

        return response()->json($types);
    }

    public function delete(Type $type) {
        $type->delete();
    }

    public function update(Request $request, Type $type) {
        $input = $request->get('type');
        $type->fill($input);
        $type->save();

        return response()->json($type);
    }
}
