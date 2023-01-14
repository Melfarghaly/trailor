<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    //
    protected $table="measurement";    
    protected $fillable = [
        'type', 'label', 'options','key','business_id'
    ];
}
