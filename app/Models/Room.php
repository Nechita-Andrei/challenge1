<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    public $incrementing=false;
    protected $primaryKey='roomnumber';
    protected $fillable=[
      'roomnumber'
    ];


    public function people(){
        return $this->hasMany(Person::class,'roomnumber');
    }
}
