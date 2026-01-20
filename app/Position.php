<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['name', 'department_id'];

    public function department()
    {
        return $this->hasOne('App\Department', 'id', 'department_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

}
