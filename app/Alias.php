<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{

	protected $fillable = [
		'domain', 'server_id', 'application_id', 'aliascode',
    ];
    

	public function application()
	{

    	return $this->belongsTo(Application::class, 'application_id');

    } 


    public function server()
	{

    	return $this->belongsTo(Server::class, 'server_id');

    } 


}
