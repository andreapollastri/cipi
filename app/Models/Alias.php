<?php

namespace App\Models;

use App\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alias extends Model
{
    use HasFactory;

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];


    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
