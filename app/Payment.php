<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $primaryKey = 'nop'; // or null
    public $incrementing = false;
    protected $table = "PAYMENT_PDL";
    protected $guarded = [];
}
