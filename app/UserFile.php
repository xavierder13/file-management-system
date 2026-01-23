<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFile extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'file_name',
        'file_path',
        'file_type',
        'title',
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
