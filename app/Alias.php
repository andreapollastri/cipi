<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{

	protected $fillable = [
        'domain',
        'application_id',
    ];

    public function application() {
        return $this->belongsTo(Application::class);
    }

}
