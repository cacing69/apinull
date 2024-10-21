<?php

namespace Modules\User;

use Illuminate\Database\Eloquent\Model;

class UserTable extends Model
{
    protected $table = 'users';
    protected $fillable = ['username', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
}
