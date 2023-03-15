<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $hidden = [
        'id',
        'password',
        'database',
        'created_at',
        'updated_at',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function aliases()
    {
        return $this->hasMany(Alias::class);
    }
}
