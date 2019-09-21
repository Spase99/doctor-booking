<?php

namespace App\Http\Controllers;

use App\Room;
use Illuminate\Http\Request;

class ApiRoomController extends Controller
{
    public function list() {
        $rooms = Room::all();
        return response()->json($rooms);
    }

    public function add(Request $request) {
        $newRoom = new Room();
        $newRoom->name = $request->get('name');
        $newRoom->save();
        return response()->json($newRoom->only('id','name'));
    }

    public function update(Room $room, Request $request) {
        $room->name = $request->get('name');
        $room->save();

        return response()->json($room->only('id','name'));
    }

    public function delete(Room $room) {
        return response()->json(['ok' => $room->delete()]);
    }
}
