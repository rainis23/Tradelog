<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    // How many dollars 1 point of price movement is worth for each futures contract.
    // Example: NQ goes from 25000 to 25010 = 10 points × $20 = $200 profit per contract.
    const POINT_VALUES = [
        'NQ'  => 20,   // E-mini Nasdaq-100
        'MNQ' => 2,    // Micro Nasdaq-100
        'ES'  => 50,   // E-mini S&P 500
        'MES' => 5,    // Micro S&P 500
        'GC'  => 100,  // Gold
    ];

    // Fields that can be filled from a form ("pnl" is not here – we calculate it ourselves)
    protected $fillable = [
        'account_id',
        'instrument',
        'direction',
        'quantity',
        'entry_price',
        'exit_price',
        'trade_date',
        'notes',
    ];

    // Laravel turns trade_date into a date object, so we can use ->format('d.m.Y')
    protected $casts = [
        'trade_date' => 'date',
    ];

    // A trade belongs to one account
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Price difference in points (positive = the trade went our way)
    public function points()
    {
        if ($this->direction == 'long') {
            return $this->exit_price - $this->entry_price;
        } else {
            return $this->entry_price - $this->exit_price;
        }
    }

    // Profit or loss in dollars = points × point value × number of contracts
    public function calculatePnl()
    {
        $pointValue = self::POINT_VALUES[$this->instrument];

        return round($this->points() * $pointValue * $this->quantity, 2);
    }
}
