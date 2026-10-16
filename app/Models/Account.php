<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    // Fields that can be filled from a form
    protected $fillable = ['name', 'broker', 'starting_balance'];

    // An account belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // An account has many trades
    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    // Current balance = starting balance + profit/loss of all trades
    public function balance()
    {
        return $this->starting_balance + $this->trades->sum('pnl');
    }
}
