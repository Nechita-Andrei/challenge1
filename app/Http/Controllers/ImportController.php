<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function index(){


        return view('import.index');
    }


    /**
     * @OA\Post(
     *   path="/import",
     *   tags={"Import"},
     *   summary="Post room information",
     *   description="Save room and people in database",
     *   @OA\RequestBody(
     *     required=true,
     *     description="Rooms info",
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *           property="file",
     *           type="string",
     *           format="binary",
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Content not valid or file is null"
     *   ),
     * )
     */
    public function import(Request $request){
        $message=[
            'message'=>  'start of import',
            'controller'=>'import',
            'method'=>'import'
        ];
        error_log(json_encode($message,JSON_PRETTY_PRINT));

        DB::table('rooms')->delete();
        $file=$request->file;
        if(is_null($file)){
            http_response_code(400);
            $error[]=[
                'code'=>400,
                'message'=>'You need to choose a CSV file for import!'
            ];
            return response()->json($error,400);
        }
        $content=file_get_contents($file->getRealPath());
        $content=explode("\n",$content);
        unset($content[count($content)-1]);
        $room_array=array();
        $people_array=array();
        $valid_affix=array('van','von','de');
        $valid_titles=array('Dr.');
        foreach ($content as $line){
            $room_to_add=new Room();
            $line=preg_replace('/\s*,\s*/',',',$line);
            $data=explode(",",$line);
            if(intval($data[0])==0){
                $message=[
                    'message'=>  'Room number is missing',
                    'controller'=>'import',
                    'method'=>'import'
                ];
                error_log(json_encode($message,JSON_PRETTY_PRINT));
                http_response_code(400);
                $error[]=[
                    'code'=>4,
                    'message'=>'CSV line is invalid! Room number is missing!'
                ];
                return response()->json($error,400);
            }
            if(in_array($data[0],$room_array)){
                $message=[
                    'message'=>  'duplicated room number found',
                    'controller'=>'import',
                    'method'=>'import'
                ];
                error_log(json_encode($message,JSON_PRETTY_PRINT));
                http_response_code(400);
                $error[]=[
                  'code'=>2,
                  'message'=>'You can not import a duplicated room number in the same file. In this case room number '.$data[0].' is duplicated.'
                ];
                return response()->json($error,400);
            }
            array_push($room_array,$data[0]);
            $room_to_add->roomnumber=$data[0];
            $room_to_add->save();
            unset($data[0]);
            $count_comma=count($data);
            while($data[count($data)]=="" && count($data)>1){
                unset($data[count($data)]);
            }
//            dd($data);
            foreach ($data as $person) {
//                dump($person);
                $person_to_add=new Person();
                $data_person = explode(" ", $person);
                $person_to_add->roomnumber=last($room_array);
                $countdata=count($data_person);
                if($countdata>3){
                    $username=preg_replace('/[( )]/','',$data_person[$countdata-1]);
                    if(in_array($username,$people_array)){
                        error_log("duplicated person found...");
                        http_response_code(400);
                        $error[]=[
                            'code'=>3,
                            'message'=>'Residents may only appear once in the same file. In this case person with ldapuser '.$username.' is duplicated.'
                        ];
                        return response()->json($error,400);
                    }
                    array_push($people_array,$username);
                    array_pop($data_person);
                    $person_to_add->ldapuser=$username;
                    $person_to_add->lastname=$data_person[$countdata-2];
                    array_pop($data_person);
                    if(in_array($data_person[$countdata-3],$valid_affix)){
                        $person_to_add->nameaffix=$data_person[$countdata-3];
                       array_pop($data_person);
                    }
                    if(in_array($data_person[0],$valid_titles)){
                        $person_to_add->title=$data_person[0];
                        $person_to_add->firstname=$data_person[1];
                        unset($data_person[0]);
                        unset($data_person[1]);
                    }else
                    {
                        $person_to_add->firstname=$data_person[0];
                        unset($data_person[0]);
                    }
                    if(count($data_person)>=1){
                        foreach ($data_person as $middlename){
                            $person_to_add->middlename.=$middlename." ";
                        }
                    }

                    $person_to_add->save();

                }elseif ($countdata==3) {
                    $person_to_add->firstname=$data_person[0];
                    $person_to_add->lastname=$data_person[1];
                    $username=preg_replace('/[( )]/','',$data_person[2]);
                    if(in_array($username,$people_array)){
                        $message=[
                            'message'=>  'duplicated person found',
                            'controller'=>'import',
                            'method'=>'import'
                        ];
                        error_log(json_encode($message,JSON_PRETTY_PRINT));
                        http_response_code(400);
                        $error[]=[
                            'code'=>3,
                            'message'=>'Residents may only appear once in the same file. In this case person with '.$username.' is duplicated.'
                        ];
                        return response()->json($error,400);
                    }
                    array_push($people_array,$username);
                    $person_to_add->ldapuser=$username;
                    $person_to_add->save();
                }elseif($count_comma<3){
                    $message=[
                        'message'=>  'too few arguments on the line',
                        'controller'=>'import',
                        'method'=>'import'
                    ];
                    error_log(json_encode($message,JSON_PRETTY_PRINT));
                    http_response_code(400);
                    $error[]=[
                      'code'=>4,
                      'message'=>'CSV line is invalid! Too few arguments on the line!'
                    ];
                    return response()->json($error,400);
                }
            }
        }
        $message=[
            'message'=>  'the import was done successfully',
            'controller'=>'import',
            'method'=>'import'
        ];
        error_log(json_encode($message,JSON_PRETTY_PRINT));
        $mesaj[]=[
            'code'=>200,
            'message'=>'The import was done successfully'
        ];
        return response()->json($mesaj,200);
    }
}
