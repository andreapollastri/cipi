<?php

namespace App\Models;

use App\Models\Cron;
use App\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Server extends Model
{
    use HasFactory;

    protected $hidden = [
        'id',
        'password',
        'database',
        'created_at',
        'updated_at'
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
