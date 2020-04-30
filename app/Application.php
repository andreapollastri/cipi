<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{

	protected $fillable = [
        'name',
        'server_id',
        'username',
        'password',
        'dbpass',
        'domain',
        'basepath',
        'php',
        'appcode',
    ];

    public function server() {
        return $this->belongsTo(Server::class, 'server_id');
    }

    public function aliases() {
    	return $this->hasMany(Alias::class);
    }

}
