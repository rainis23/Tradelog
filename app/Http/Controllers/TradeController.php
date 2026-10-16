<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Full CRUD for trades: list, create, edit, delete.
class TradeController
{
    // GET /trades – the list of trades.
    // The filters on the page call the same URL with AJAX – then we send back only the table.
    public function index(Request $request)
    {
        $accounts = Auth::user()->accounts;

        // Start with all trades of my accounts ...
        $query = Trade::whereIn('account_id', $accounts->pluck('id'));

        // ... and add a filter for everything the user chose
        if ($request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->instrument) {
            $query->where('instrument', $request->instrument);
        }

        if ($request->result == 'win') {
            $query->where('pnl', '>', 0);
        }

        if ($request->result == 'loss') {
            $query->where('pnl', '<=', 0);
        }

        if ($request->search) {
            $query->where('notes', 'like', '%' . $request->search . '%');
        }

        $trades = $query->orderBy('trade_date', 'desc')->orderBy('id', 'desc')->get();

        // AJAX request (sent by filterTrades() in trades.blade.php) → only the table
        if ($request->ajax()) {
            return view('trades-table', ['trades' => $trades]);
        }

        // Normal request → the whole page
        return view('trades', [
            'trades' => $trades,
            'accounts' => $accounts,
        ]);
    }

    // GET /trades/create – empty form
    public function create()
    {
        $accounts = Auth::user()->accounts;

        // A trade needs an account
        if ($accounts->count() == 0) {
            return redirect()->route('dashboard')->with('success', 'Add an account first.');
        }

        // An empty trade with some default values for the form
        $trade = new Trade();
        $trade->direction = 'long';
        $trade->quantity = 1;
        $trade->trade_date = today();

        return view('trade-form', [
            'trade' => $trade,
            'accounts' => $accounts,
        ]);
    }

    // POST /trades – save a new trade
    public function store(Request $request)
    {
        $data = $this->validateTrade($request);

        $trade = new Trade($data);
        $trade->pnl = $trade->calculatePnl();
        $trade->save();

        return redirect()->route('trades.index')->with('success', 'Trade saved!');
    }

    // GET /trades/5/edit – the same form, filled with the trade's values
    public function edit(Trade $trade)
    {
        $this->checkOwner($trade);

        return view('trade-form', [
            'trade' => $trade,
            'accounts' => Auth::user()->accounts,
        ]);
    }

    // PUT /trades/5 – save the changes
    public function update(Request $request, Trade $trade)
    {
        $this->checkOwner($trade);

        $data = $this->validateTrade($request);

        $trade->fill($data);
        $trade->pnl = $trade->calculatePnl();   // the prices may have changed, so calculate again
        $trade->save();

        return redirect()->route('trades.index')->with('success', 'Trade updated!');
    }

    // DELETE /trades/5 – called with AJAX from the trades table, answers with JSON
    public function destroy(Trade $trade)
    {
        $this->checkOwner($trade);

        $trade->delete();

        return response()->json(['message' => 'Trade deleted.']);
    }

    // ---------- Helper functions (used by the functions above) ----------

    // Checks the form. If something is wrong, Laravel goes back to the form and shows the errors.
    private function validateTrade(Request $request)
    {
        $data = $request->validate([
            'account_id' => 'required|integer',
            'instrument' => 'required|in:NQ,MNQ,ES,MES,GC',
            'direction' => 'required|in:long,short',
            'quantity' => 'required|integer|min:1|max:100',
            'entry_price' => 'required|numeric|min:0.01',
            'exit_price' => 'required|numeric|min:0.01',
            'trade_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|max:1000',
        ]);

        // The account must be MY account (otherwise: error 404)
        Auth::user()->accounts()->findOrFail($data['account_id']);

        return $data;
    }

    // Stops with "403 Forbidden" if the trade belongs to another user
    private function checkOwner(Trade $trade)
    {
        if ($trade->account->user_id != Auth::id()) {
            abort(403);
        }
    }
}
