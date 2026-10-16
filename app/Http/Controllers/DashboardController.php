<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// The dashboard (main page): numbers, chart and the list of accounts.
class DashboardController
{
    // GET /
    public function index()
    {
        $accounts = Auth::user()->accounts;

        // All trades of all my accounts
        $trades = Trade::whereIn('account_id', $accounts->pluck('id'))->get();

        // Win rate = how many % of the trades made money
        $wins = $trades->where('pnl', '>', 0)->count();

        if ($trades->count() > 0) {
            $winRate = round($wins / $trades->count() * 100);
        } else {
            $winRate = 0;
        }

        return view('dashboard', [
            'accounts' => $accounts,
            'tradeCount' => $trades->count(),
            'totalProfit' => $trades->sum('pnl'),
            'winRate' => $winRate,
            'bestTrade' => $trades->max('pnl') ?? 0,
        ]);
    }

    // GET /chart?account_id=3 – AJAX.
    // Returns JSON for the chart: the total profit after every trade.
    public function chart(Request $request)
    {
        $accountIds = Auth::user()->accounts->pluck('id');

        $query = Trade::whereIn('account_id', $accountIds);

        if ($request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        $trades = $query->orderBy('trade_date')->orderBy('id')->get();

        $labels = [];   // dates under the chart
        $values = [];   // height of the line
        $total = 0;

        foreach ($trades as $trade) {
            $total = $total + $trade->pnl;
            $labels[] = $trade->trade_date->format('d.m');
            $values[] = round($total, 2);
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }
}
