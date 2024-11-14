<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    protected $table = 'users';
    protected $primaryKey = "id";
    protected $fillable = ['username', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    const DELETED_AT = "deleted_at";
    const UPDATED_AT = "updated_at";
    const CREATED_AT = "created_at";

    protected $dates =[
        "created_at",
        "updated_at",
        "deleted_at"
    ];


}
