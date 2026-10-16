<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Add and delete trading accounts (both buttons are on the dashboard).
class AccountController
{
    // POST /accounts – save a new account
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:100',
            'broker' => 'nullable|max:100',
            'starting_balance' => 'required|numeric|min:0|max:100000000',
        ]);

        // Creating it through the user fills in user_id automatically
        Auth::user()->accounts()->create($data);

        return redirect()->route('dashboard')->with('success', 'Account added!');
    }

    // DELETE /accounts/5 – delete an account
    public function destroy(Account $account)
    {
        // Only the owner may delete it
        if ($account->user_id != Auth::id()) {
            abort(403);
        }

        // The database also deletes all trades of this account (onDelete('cascade') in the migration)
        $account->delete();

        return redirect()->route('dashboard')->with('success', 'Account deleted.');
    }
}
