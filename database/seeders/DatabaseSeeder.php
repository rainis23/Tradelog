<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Fills the database with demo data.
// Run:  php artisan db:seed                     (start.bat does this)
// Or delete everything and start again:  php artisan migrate:fresh --seed   (reset-database.bat)
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // If the demo user is already there, the demo data is already there – don't add it twice.
        if (User::where('email', 'demo@tradelog.test')->exists()) {
            return;
        }

        // ---------- 1. Users ----------
        $demo = User::create([
            'name' => 'Demo Trader',
            'email' => 'demo@tradelog.test',
            'password' => Hash::make('password'),
        ]);

        // A second user – to show that a user only sees their own data
        $second = User::create([
            'name' => 'Second Trader',
            'email' => 'second@tradelog.test',
            'password' => Hash::make('password'),
        ]);

        // ---------- 2. Accounts ----------
        // [owner, account name, broker, starting balance]
        $accounts = [
            [$demo, 'Apex 50K Evaluation', 'Apex Trader Funding', 50000],
            [$demo, 'Topstep 100K Funded', 'Topstep', 100000],
            [$demo, 'Personal Futures', 'Tradovate', 25000],
            [$demo, 'Sim Practice', 'NinjaTrader', 25000],
            [$second, 'Second Trader Live', 'Interactive Brokers', 20000],
        ];

        foreach ($accounts as $data) {
            $account = new Account();
            $account->user_id = $data[0]->id;
            $account->name = $data[1];
            $account->broker = $data[2];
            $account->starting_balance = $data[3];
            $account->save();
        }

        // ---------- 3. Trades ----------
        // Use the same "random" numbers every time, so the demo always looks the same.
        mt_srand(18);

        // Typical price of each contract and how many points a trade usually moves
        $markets = [
            'NQ'  => ['price' => 25000, 'move' => 40],
            'MNQ' => ['price' => 25000, 'move' => 40],
            'ES'  => ['price' => 6700, 'move' => 12],
            'MES' => ['price' => 6700, 'move' => 12],
            'GC'  => ['price' => 4000, 'move' => 15],
        ];

        $notes = [
            'Clean setup, followed my plan.',
            'Entered too early, should have waited.',
            'Took profit at the first target.',
            'News made the market very fast.',
            'Chased the move – avoid this next time.',
            'Waited for the retest, good entry.',
            null,
            null,
        ];

        // 25 random trades for every account
        foreach (Account::all() as $account) {
            for ($i = 1; $i <= 25; $i++) {
                $instrument = array_rand($markets);          // random key, e.g. "NQ"
                $market = $markets[$instrument];

                $direction = rand(0, 1) == 1 ? 'long' : 'short';
                $entry = $market['price'] + rand(-300, 300);

                // How many points the trade made. About 55 of 100 trades are winners.
                $points = rand(5, $market['move']);
                if (rand(1, 100) > 55) {
                    $points = -$points;   // a losing trade
                }

                // A long trade wins when the price goes UP, a short trade when it goes DOWN
                if ($direction == 'long') {
                    $exit = $entry + $points;
                } else {
                    $exit = $entry - $points;
                }

                // A random day in the last 60 days. Futures are traded Monday to Friday,
                // so a Saturday or Sunday is moved back 2 days.
                $date = now()->subDays(rand(1, 60));
                if ($date->isWeekend()) {
                    $date->subDays(2);
                }

                $trade = new Trade();
                $trade->account_id = $account->id;
                $trade->instrument = $instrument;
                $trade->direction = $direction;
                $trade->quantity = rand(1, 3);
                $trade->entry_price = $entry;
                $trade->exit_price = $exit;
                $trade->trade_date = $date;
                $trade->notes = $notes[array_rand($notes)];
                $trade->pnl = $trade->calculatePnl();
                $trade->save();
            }
        }
    }
}
