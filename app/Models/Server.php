<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;

    protected $hidden = [
        'id',
        'password',
        'database',
        'created_at',
        'updated_at',
    ];

    public function sites()
    {
        return $this->hasMany(Site::class)->where('panel', false);
    }

    public function allsites()
    {
        return $this->hasMany(Site::class);
    }
}
