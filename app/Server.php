<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{

    protected $fillable = [
        'id',
        'name',
        'provider',
        'location',
        'ip',
        'port',
        'username',
        'password',
        'dbroot',
        'status',
        'servercode',
    ];


    public function applications() {
    	return $this->hasMany(Application::class);
    }

}
