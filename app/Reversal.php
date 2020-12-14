<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reversal extends Model
{
    //
    protected $primaryKey = 'nop'; // or null
    public $incrementing = false;
    protected $table = "REVERSAL_PDL";
    protected $guarded = [];
}
