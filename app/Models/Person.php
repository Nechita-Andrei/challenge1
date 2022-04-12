<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    public $incrementing=false;
    protected $primaryKey='ldapuser';

    protected $fillable=[
        'ldapuser',
        'firstname',
        'lastname',
        'middlename',
        'title',
        'nameaffix',
        'roomnumber'

    ];

    public function room(){
        return $this->belongsTo(Room::class,'roomnumber');
    }
}
