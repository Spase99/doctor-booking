<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opening extends Model
{
    use SoftDeletes;

    protected $table = 'openings';
}
