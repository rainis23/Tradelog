@extends('layout')

@section('title', 'Dashboard')

@section('content')

    <h1>Dashboard</h1>
    <p class="muted">Hello, {{ Auth::user()->name }}! Here is how your trading is going.</p>
    <br>

    {{-- The main numbers (calculated in DashboardController@index) --}}
    <div class="grid">
        <div class="card">
            <div class="stat-label">Total profit</div>
            <div class="stat-value {{ $totalProfit >= 0 ? 'green' : 'red' }}">{{ number_format($totalProfit, 2) }} $</div>
        </div>
        <div class="card">
            <div class="stat-label">Win rate</div>
            <div class="stat-value">{{ $winRate }}%</div>
        </div>
        <div class="card">
            <div class="stat-label">Trades</div>
            <div class="stat-value">{{ $tradeCount }}</div>
        </div>
        <div class="card">
            <div class="stat-label">Best trade</div>
            <div class="stat-value green">{{ number_format($bestTrade, 2) }} $</div>
        </div>
    </div>

    {{-- The chart. Its data comes with AJAX from DashboardController@chart (see loadChart() below). --}}
    @if ($tradeCount > 0)
        <div class="card">
            <div class="page-header">
                <h2>Profit over time</h2>
                <select id="chart-account" onchange="loadChart()" style="width: auto;">
                    <option value="">All accounts</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <canvas id="chart" height="90"></canvas>
        </div>
    @endif

    <div class="two-columns">

        {{-- My accounts --}}
        <div class="card table-wrapper">
            <h2>My accounts</h2>

            @if ($accounts->count() == 0)
                <p>You have no accounts yet. Add your first one →</p>
            @else
                <table>
                    <tr>
                        <th>Account</th>
                        <th class="number">Start</th>
                        <th class="number">Balance now</th>
                        <th class="number">Profit</th>
                        <th class="number">Trades</th>
                        <th></th>
                    </tr>
                    @foreach ($accounts as $account)
                        <tr>
                            <td>
                                {{-- Opens the trades page, already filtered by this account --}}
                                <a href="{{ route('trades.index', ['account_id' => $account->id]) }}">{{ $account->name }}</a>
                                <div class="muted small">{{ $account->broker }}</div>
                            </td>
                            <td class="number">{{ number_format($account->starting_balance, 2) }} $</td>
                            <td class="number"><strong>{{ number_format($account->balance(), 2) }} $</strong></td>
                            <td class="number {{ $account->trades->sum('pnl') >= 0 ? 'green' : 'red' }}">
                                {{ number_format($account->trades->sum('pnl'), 2) }} $
                            </td>
                            <td class="number">{{ $account->trades->count() }}</td>
                            <td>
                                {{-- A form can only send GET or POST. @method('DELETE') tells Laravel it is a DELETE. --}}
                                <form method="POST" action="{{ route('accounts.destroy', $account) }}"
                                      onsubmit="return confirm('Delete this account and ALL its trades?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-button">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        {{-- Add a new account (sent to AccountController@store) --}}
        <div class="card">
            <h2>Add account</h2>

            <form method="POST" action="{{ route('accounts.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g. Apex 50K Evaluation">
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="broker">Broker (optional)</label>
                    <input type="text" name="broker" id="broker" value="{{ old('broker') }}" placeholder="e.g. Tradovate">
                    @error('broker')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="starting_balance">Starting balance ($)</label>
                    <input type="number" step="0.01" name="starting_balance" id="starting_balance" value="{{ old('starting_balance') }}" placeholder="50000">
                    @error('starting_balance')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn">Add account</button>
            </form>
        </div>

    </div>

@endsection

@section('scripts')
    {{-- Chart.js library (saved in public/js, so it also works without internet) --}}
    <script src="{{ asset('js/chart.min.js') }}"></script>

    <script>
        let chart = null;

        // AJAX: get the chart data (JSON) from DashboardController@chart and draw the chart
        function loadChart() {
            let accountId = document.getElementById('chart-account').value;

            fetch("{{ route('chart') }}?account_id=" + accountId)
                .then(response => response.json())
                .then(data => {
                    // remove the old chart before drawing the new one
                    if (chart != null) {
                        chart.destroy();
                    }

                    chart = new Chart(document.getElementById('chart'), {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Total profit ($)',
                                data: data.values,
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22, 163, 74, 0.1)',
                                fill: true,
                                pointRadius: 2
                            }]
                        }
                    });
                });
        }

        // Draw the chart when the page opens (only if there is a chart on the page)
        if (document.getElementById('chart')) {
            loadChart();
        }
    </script>
@endsection
