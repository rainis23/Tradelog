# TradeLog – a trading journal (school project)

A Laravel website where a trader saves trades. You register, add your trading accounts
and save every trade. The website calculates the profit or loss of each trade, the balance of
every account, the win rate, and draws a chart of your profit.

**Demo login:** `demo@tradelog.test` / `password`

---

## 1. Run it on Windows (XAMPP)

1. Install **XAMPP** (PHP 8.2 or newer) from <https://www.apachefriends.org> into `C:\xampp`.
2. Open the **XAMPP Control Panel** and click **Start** next to **MySQL**.
3. Unzip the `tradelog` folder anywhere (for example `C:\xampp\htdocs\tradelog`).
4. Double-click **`start.bat`**. The first time it creates the database `tradelog`, the tables and
   the demo data. Then the site opens at <http://127.0.0.1:8000> (press F5 if it is not there yet).
   Keep the black window open – closing it stops the site.

| I want to… | How |
|---|---|
| Start again with fresh demo data | double-click `reset-database.bat` |
| See the database | start Apache in XAMPP → <http://localhost/phpmyadmin> → database `tradelog` |
| Use XAMPP's Apache instead of start.bat | folder in `C:\xampp\htdocs\tradelog`, Apache + MySQL started, open <http://localhost/tradelog/public> |
| See all URLs of the site | `C:\xampp\php\php.exe artisan route:list --except-vendor` |

**Problems?** `start.bat` tells you what is wrong: XAMPP not found, MySQL not running, or a
PHP extension that is off (then: XAMPP → Apache **Config** → **PHP (php.ini)** → remove the `;`
in front of `extension=pdo_mysql`, save, run `start.bat` again).

---

## 2. Where is what – all the files we wrote

You only need these files. Everything else (`vendor/`, `storage/`, `bootstrap/`, `artisan`,
`public/index.php`) is Laravel itself and you never change it.

| File | What it does |
|---|---|
| `routes/web.php` | **All URLs** of the site. Each line: URL → controller function |
| `app/Http/Controllers/AuthController.php` | Log in, register, log out |
| `app/Http/Controllers/DashboardController.php` | Dashboard numbers + chart data (JSON for AJAX) |
| `app/Http/Controllers/AccountController.php` | Add and delete an account |
| `app/Http/Controllers/TradeController.php` | **CRUD for trades** + the AJAX filter |
| `app/Models/User.php` | User – has many accounts |
| `app/Models/Account.php` | Account – belongs to a user, has many trades, `balance()` |
| `app/Models/Trade.php` | Trade – belongs to an account, **profit calculation** |
| `database/migrations/` | The 3 tables: `users`, `accounts`, `trades` |
| `database/seeders/DatabaseSeeder.php` | Demo data: 2 users, 5 accounts, 125 trades |
| `resources/views/layout.blade.php` | The frame of every page (menu at the top) |
| `resources/views/login.blade.php`, `register.blade.php` | Log in / register forms |
| `resources/views/dashboard.blade.php` | Dashboard + chart JavaScript (AJAX 3) |
| `resources/views/trades.blade.php` | Trades page + filter/delete JavaScript (AJAX 1 and 2) |
| `resources/views/trades-table.blade.php` | Only the trades table (also sent alone by AJAX) |
| `resources/views/trade-form.blade.php` | One form for **new** and **edit** trade |
| `public/css/style.css` | All the design (plain CSS) |
| `public/js/chart.min.js` | Chart.js library (not ours – draws the chart) |
| `.env.example` | Settings (database name, user …) – copied to `.env` by start.bat |
| `start.bat`, `reset-database.bat` | Double-click helpers for Windows |

---

## 3. How it was built – step by step

**Step 1 – Read the requirements and choose the topic.**
The task needs: login, 2 related models, full CRUD, seeders, AJAX, Blade, validation.
We chose a **trading journal**: it is about money (financial topic) and it naturally has
2 related things – *accounts* and *trades* (one account has many trades).

**Step 2 – Plan the database first.** Before any code we decided which tables we need:
```
users ──< accounts ──< trades        (──< means "has many")
```
- `accounts`: name, broker, starting_balance, user_id
- `trades`: instrument, direction (long/short), quantity, entry_price, exit_price, pnl, trade_date, notes, account_id

`pnl` (profit and loss) is **saved** in the table, so we can filter "only wins" with SQL
(`where('pnl', '>', 0)`) and add it up quickly.

**Step 3 – Migrations** (`database/migrations/`) create these tables. `foreignId(...)->constrained()`
makes the link between tables, `onDelete('cascade')` deletes the trades when their account is deleted.

**Step 4 – Models** (`app/Models/`) are PHP classes for the tables. Here we wrote the relationships
(`hasMany`, `belongsTo`) and the **profit formula** in `Trade.php`:
`points × point value × contracts`. Example: NQ long, 2 contracts, 25100 → 25118.25 =
18.25 points × $20 × 2 = **$730**.

**Step 5 – Routes** (`routes/web.php`). One line per URL. Pages for guests (login, register) are in a
`guest` group, everything else is in an `auth` group, so you must be logged in.

**Step 6 – Controllers.** One controller per topic: login (`AuthController`), dashboard,
accounts, trades. Every function is short and does one thing: check the input, ask the database,
return a page.

**Step 7 – Views (Blade).** One `layout.blade.php` with the menu; every page `@extends` it.
The new-trade and edit-trade pages are the **same file** (`trade-form.blade.php`) – it checks
if the trade already has an id.

**Step 8 – Validation.** Every form is checked on the server with `$request->validate([...])`.
If something is wrong, Laravel goes back to the form and `@error(...)` shows the message.

**Step 9 – AJAX.** Three places where JavaScript talks to the server without reloading the page:
filter the trades table, delete a trade, load the chart data. We use the browser's `fetch()`.

**Step 10 – Security.** Every controller function checks that the trade/account belongs to the
logged-in user (otherwise `abort(403)`). Passwords are hashed. Every form has `@csrf`.

**Step 11 – Seeders.** `DatabaseSeeder.php` fills the database with demo data, so the site
does not look empty when we show it. `mt_srand(18)` makes the "random" trades the same every time.

**Step 12 – Design.** One plain CSS file, no frameworks. It also works on a phone.

**Step 13 – Make it easy to run.** XAMPP gives PHP and MySQL. `start.bat` creates the
settings file and the database and starts the site. Laravel itself (`vendor/`) is already
in the zip, so nothing else has to be installed.

---

## 4. How each requirement works

### Authentication – `AuthController.php`
```php
if (Auth::attempt($data)) {                 // finds the user by email, checks the hashed password
    $request->session()->regenerate();      // new session id (security)
    return redirect()->route('dashboard');
}
return back()->withErrors(['email' => 'Wrong email or password.']);
```
- Register: `Hash::make($data['password'])` saves the password **hashed**, never as plain text.
- `Route::middleware('auth')` in `routes/web.php` protects the pages. Guests are sent to `/login`.
- It uses Laravel's own login system (`Auth`) – the same functions Laravel Breeze uses.
  Breeze generates 30+ files (password reset, email verification, profile …); we only need
  login, register and logout, so they are in one small controller.

### 2 related models – `Account.php`, `Trade.php`
```php
// Account.php                                   // Trade.php
public function trades()                         public function account()
{                                                {
    return $this->hasMany(Trade::class);             return $this->belongsTo(Account::class);
}                                                }
```
Thanks to this we can write `$account->trades` or `$trade->account->name`.
The dashboard shows it: every account with its own trades, balance and profit.

### Full CRUD – `TradeController.php` + `routes/web.php`

| | URL | Function | Page |
|---|---|---|---|
| **C**reate | GET `/trades/create` → POST `/trades` | `create()` → `store()` | `trade-form.blade.php` |
| **R**ead | GET `/trades` | `index()` | `trades.blade.php` |
| **U**pdate | GET `/trades/5/edit` → PUT `/trades/5` | `edit()` → `update()` | `trade-form.blade.php` |
| **D**elete | DELETE `/trades/5` | `destroy()` | Delete button (AJAX) |

```php
public function store(Request $request)
{
    $data = $this->validateTrade($request);     // 1. check the form
    $trade = new Trade($data);                  // 2. make the trade
    $trade->pnl = $trade->calculatePnl();       // 3. calculate the profit
    $trade->save();                             // 4. save it in the database
    return redirect()->route('trades.index')->with('success', 'Trade saved!');
}
```
HTML forms only know GET and POST, so the edit form adds `@method('PUT')`.

### Seeders – `DatabaseSeeder.php`
Three parts: 1) 2 users, 2) 5 accounts, 3) 25 random trades per account (`for` loop + `rand()`).
Run with `php artisan db:seed`, or `php artisan migrate:fresh --seed` to delete everything and start again.

### AJAX – `fetch()` in `trades.blade.php` and `dashboard.blade.php`

**1. Filter the trades** – change a filter → `filterTrades()`:
```js
fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })   // "this is AJAX"
    .then(response => response.text())
    .then(html => {
        document.getElementById('trades-table').innerHTML = html;  // replace only the table
    });
```
In `TradeController@index` every chosen filter adds a `where()`. Then:
```php
if ($request->ajax()) {
    return view('trades-table', ['trades' => $trades]);   // AJAX → only the table
}
return view('trades', [...]);                             // normal → the whole page
```

**2. Delete a trade** – `deleteTrade(id)` sends `method: 'DELETE'` with the CSRF token.
`TradeController@destroy` answers with **JSON** `{"message": "Trade deleted."}`, the JavaScript
shows the message and loads the table again.

**3. The chart** – `loadChart()` gets JSON from `/chart?account_id=…`:
`{"labels": ["14.08", …], "values": [450, 210, …]}` (`DashboardController@chart` adds up the profit
after every trade). Chart.js draws the line. Changing the account in the dropdown loads new data.

### Blade – `resources/views/`
| Blade | What it does |
|---|---|
| `@extends('layout')`, `@section('content')`, `@yield('content')` | every page uses the same frame |
| `@include('trades-table')` | puts the table file inside the trades page |
| `{{ $trade->notes }}` | prints a value (HTML is escaped → safe against XSS) |
| `@foreach`, `@if / @else` | loops and conditions |
| `@auth / @else` | different menu for logged-in users and guests |
| `@csrf`, `@method('PUT')` | security token, PUT/DELETE forms |
| `@error('name')`, `old('name')` | validation message, keep what the user typed |

### Validation – `$request->validate([...])`
```php
'instrument' => 'required|in:NQ,MNQ,ES,MES,GC',      // only these contracts
'quantity'   => 'required|integer|min:1|max:100',
'entry_price'=> 'required|numeric|min:0.01',
'trade_date' => 'required|date|before_or_equal:today', // not in the future
```
Also in `AuthController` (`email|unique:users`, `min:8|confirmed`) and `AccountController`.
Show it in class: type 500 contracts, a negative price or a date in the future → Save.

---

## 5. Questions the teacher may ask

| Question | Answer |
|---|---|
| What is MVC? | **M**odel = data (`app/Models`), **V**iew = HTML (`resources/views`), **C**ontroller = logic (`app/Http/Controllers`) |
| What happens when I open `/trades`? | `routes/web.php` → `TradeController@index` → gets the trades from the database → returns `trades.blade.php` |
| What is a migration? | PHP code that creates a table. `php artisan migrate` runs them |
| Migration vs seeder? | Migration makes the **tables**, seeder fills them with **data** |
| What is Eloquent? | Laravel's way to use the database with PHP objects: `Trade::where(...)->get()`, `$trade->save()` |
| What is `$fillable`? | The fields that can be filled from a form. `pnl` is not there – we calculate it |
| What is `Trade $trade` in `edit(Trade $trade)`? | Route model binding – Laravel finds the trade by the id in the URL (or shows 404) |
| How does a user only see their own trades? | Every query starts with `whereIn('account_id', my account ids)`, and edit/update/delete check the owner → 403 |
| What is CSRF / `@csrf`? | A secret token in every form, so other websites can't send forms in your name |
| How is the password stored? | Hashed with `Hash::make()` (bcrypt). `Auth::attempt()` compares the hashes |
| What is JSON? | A text format for data: `{"message": "Trade deleted."}`. JavaScript reads it with `response.json()` |
| How does Laravel know a request is AJAX? | Our `fetch()` sends the header `X-Requested-With: XMLHttpRequest` → `$request->ajax()` is true |
| What happens to trades when an account is deleted? | The database deletes them too – `onDelete('cascade')` in the migration |
| Why no Laravel Breeze? | Breeze makes 30+ files we don't need. We use the same Laravel `Auth` functions in one controller |
| Where is the design? | `public/css/style.css` – one plain CSS file |

---

## 6. Show it in class (3 minutes)

1. **Register** a new user → empty dashboard → add an account (try it empty first → validation errors).
2. Log out, log in as `demo@tradelog.test` / `password`.
3. **Dashboard:** numbers and chart. Change the account in the chart dropdown → AJAX
   (F12 → Network → you can see the JSON).
4. **Trades:** change the filters and type in the search → the table changes without reloading (AJAX).
   **Delete** a trade → it disappears, a message appears (AJAX + JSON).
5. **+ New trade:** click Save with wrong data → errors. Fill it in → Save → the profit is calculated.
   **Edit** it, change the exit price → the profit changes. That is full CRUD.
6. Log in as `second@tradelog.test` and open the edit URL of a demo trade → **403 Forbidden**.
7. phpMyAdmin → database `tradelog` → the tables `users`, `accounts`, `trades`.

---

## 7. Git (both of us must commit)

```
git init
git add .
git commit -m "TradeLog school project"
git branch -M main
git remote add origin https://github.com/<your-name>/tradelog.git
git push -u origin main
```
Then your mate runs `git clone …`, makes a change, `git commit` and `git push`.
`vendor/` and `.env` are not uploaded (see `.gitignore`); `start.bat` installs `vendor/` with
Composer when it is missing.
