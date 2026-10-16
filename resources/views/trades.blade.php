@extends('layout')

@section('title', 'Trades')

@section('content')

    <div class="page-header">
        <div>
            <h1>Trades</h1>
            <p class="muted">Change a filter – the table updates with AJAX, without reloading the page.</p>
        </div>
        <a href="{{ route('trades.create') }}" class="btn">+ New trade</a>
    </div>

    {{-- Filters. Every change calls the JavaScript function filterTrades() (at the bottom). --}}
    <div class="card filters">
        <input type="text" id="search" placeholder="Search in notes..." onkeyup="filterTrades()">

        <select id="account" onchange="filterTrades()">
            <option value="">All accounts</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(request('account_id') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>

        <select id="instrument" onchange="filterTrades()">
            <option value="">All contracts</option>
            @foreach (App\Models\Trade::POINT_VALUES as $symbol => $pointValue)
                <option value="{{ $symbol }}">{{ $symbol }}</option>
            @endforeach
        </select>

        <select id="result" onchange="filterTrades()">
            <option value="">Wins and losses</option>
            <option value="win">Only wins</option>
            <option value="loss">Only losses</option>
        </select>
    </div>

    {{-- Message after an AJAX delete (hidden at the start) --}}
    <div id="message" class="alert" style="display: none;"></div>

    {{-- The table. JavaScript replaces everything inside this div. --}}
    <div id="trades-table">
        @include('trades-table')
    </div>

@endsection

@section('scripts')
    <script>
        // AJAX 1: load the table again with the chosen filters
        function filterTrades() {
            let url = "{{ route('trades.index') }}"
                + "?search=" + encodeURIComponent(document.getElementById('search').value)
                + "&account_id=" + document.getElementById('account').value
                + "&instrument=" + document.getElementById('instrument').value
                + "&result=" + document.getElementById('result').value;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }   // tells Laravel this is AJAX
            })
                .then(response => response.text())
                .then(html => {
                    // TradeController@index sent back only the table – put it on the page
                    document.getElementById('trades-table').innerHTML = html;
                });
        }

        // AJAX 2: delete a trade without reloading the page
        function deleteTrade(id) {
            if (!confirm('Do you really want to delete this trade?')) {
                return;
            }

            fetch("{{ url('trades') }}/" + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',   // Laravel needs this token for security
                    'Accept': 'application/json'            // we want a JSON answer
                }
            })
                .then(response => response.json())
                .then(data => {
                    // show the message from the JSON answer, e.g. "Trade deleted."
                    let message = document.getElementById('message');
                    message.innerText = data.message;
                    message.style.display = 'block';

                    filterTrades();   // load the table again (the numbers change too)
                });
        }
    </script>
@endsection
