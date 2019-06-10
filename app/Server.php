<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'name', 'provider', 'location', 'ip', 'port', 'username', 'password', 'dbroot',	'secretkey', 'servercode',
    ];


    public function applications()
    {

    	return $this->hasMany(Application::class);

    } 


    public function aliases()
    {

        return $this->hasMany(Alias::class);

    } 


}
