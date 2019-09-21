<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor extends User
{
    protected $table = 'users';
    protected $fillable = ['name', 'phone', 'email', 'url_slug'];

    public function types() {
        return $this->hasMany('App\Type');
    }

}
