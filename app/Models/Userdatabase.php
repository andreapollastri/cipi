<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdatabase extends Model
{
    use HasFactory;


public function mysqluser()
{
    return $this->belongsTo(Mysqluser::class, 'mysqluser_id', 'id');
}

}


