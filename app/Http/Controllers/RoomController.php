<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * @OA\Get(
     *      path="/room/{roomnumber}",
     *      tags={"Room"},
     *      summary="Get a room information",
     *      description="Returns information about the room and about the people in there",
     *      @OA\Parameter(
     *          name="roomnumber",
     *          description="Room number",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *     ),
     *      @OA\Response(
     *          response=404,
     *          description="Room not found or room number is incorrect"
     *      )
     *
     * )
     * @OA\Get(
     *      path="/room",
     *      tags={"Room"},
     *      summary="Get all rooms informations",
     *      description="Returns all data from rooms",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *     ),
     * )
     */
    public function details($roomnumber = false)
    {
        $message=[
            'message'=>  'start of details for room',
            'controller'=>'room',
            'method'=>'details'
        ];
        error_log(json_encode($message,JSON_PRETTY_PRINT));
        $json_room = [];
        $json_people = [];
        $final_json = array();
        if (!$roomnumber) {
            $rooms = Room::all();
            foreach ($rooms as $room) {
                foreach ($room->people as $person) {
                    $json_people[] = [
                        'first name' => $person->firstname,
                        'last name' => $person->lastname,
                        'title' => $person->title,
                        'name addition' => $person->nameaffix,
                        'ldapuser' => $person->ldapuser


                    ];

                }
                $json_room[] = [

                    'room' => $room->roomnumber,
                    'people' => $json_people
                ];
                array_push($final_json, json_encode($json_room, JSON_PRETTY_PRINT));
            }
        } else {

            $room = Room::find($roomnumber);
            if (is_null($room) && strlen($roomnumber) == 4) {
                $message=[
                    'message'=>  'room number is not found',
                    'controller'=>'room',
                    'method'=>'details'
                ];
                error_log($message,JSON_PRETTY_PRINT);
                http_response_code(404);
                $error[] = [
                    'code' => 5,
                    'message' => 'The room with the specified number does not exist. In this case room number ' . $roomnumber . ' does not exist.'
                ];
                return response()->json($error, 404);
            }
            if (strlen($roomnumber) != 4) {
                $message=[
                    'message'=>  'room number is incorrect',
                    'controller'=>'room',
                    'method'=>'details'
                ];
                error_log($message,JSON_PRETTY_PRINT);
                http_response_code(404);
                $error[] = [
                    'code' => 6,
                    'message' => 'Every room number must have four digits. In this case room number ' . $roomnumber . ' is incorrect.'
                ];
                return response()->json($error, 404);
            }
            foreach ($room->people as $person) {
                $json_people[] = [
                    'first name' => $person->firstname,
                    'last name' => $person->lastname,
                    'title' => $person->title,
                    'name addition' => $person->nameaffix,
                    'ldapuser' => $person->ldapuser


                ];

            }
            $json_room[] = [

                'room' => $room->roomnumber,
                'people' => $json_people
            ];
            array_push($final_json, json_encode($json_room, JSON_PRETTY_PRINT));
        }

        $message=[
          'message'=>  'end of details successfully',
            'controller'=>'room',
            'method'=>'details'
        ];
        error_log(json_encode($message,JSON_PRETTY_PRINT));
        return view('room.details', ['jsons' => $final_json]);
    }
}
