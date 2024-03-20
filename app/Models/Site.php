<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'domain',
        'username',
        'password',
        'basepath',
        'repository',
        'branch',
        'php',
        'supervisor',
        'nginx',
        'deploy',
    ];
}
