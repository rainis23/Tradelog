<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

// A user who can log in. "Authenticatable" gives Laravel's login functions.
class User extends Authenticatable
{
    // Fields that can be filled with User::create([...])
    protected $fillable = ['name', 'email', 'password'];

    // Never send the password anywhere (for example in JSON)
    protected $hidden = ['password'];

    // A user has many trading accounts
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
