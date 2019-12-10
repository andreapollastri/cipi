<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{

	protected $fillable = [
		'domain', 'server_id', 'username', 'password', 'dbpass', 'basepath', 'autoinstall', 'appcode',
    ];


    public function server()
    {

        return $this->belongsTo(Server::class, 'server_id');

    }



    public function aliases()
    {

    	return $this->hasMany(Alias::class);

    }




}
