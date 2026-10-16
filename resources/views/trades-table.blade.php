{{-- The table of trades.
     It is used in two places:
       1. on the trades page:  @include('trades-table')
       2. alone, for AJAX:     TradeController@index returns only this file --}}

<p>
    <strong>{{ $trades->count() }}</strong> trades &nbsp;·&nbsp;
    Total profit:
    <strong class="{{ $trades->sum('pnl') >= 0 ? 'green' : 'red' }}">{{ number_format($trades->sum('pnl'), 2) }} $</strong>
</p>

@if ($trades->count() == 0)

    <div class="card">No trades found.</div>

@else

    <div class="card table-wrapper" style="padding: 0;">
        <table>
            <tr>
                <th>Date</th>
                <th>Account</th>
                <th>Contract</th>
                <th>Side</th>
                <th class="number">Qty</th>
                <th class="number">Entry</th>
                <th class="number">Exit</th>
                <th class="number">Profit</th>
                <th>Notes</th>
                <th></th>
            </tr>

            @foreach ($trades as $trade)
                <tr>
                    <td>{{ $trade->trade_date->format('d.m.Y') }}</td>
                    <td>{{ $trade->account->name }}</td>
                    <td><strong>{{ $trade->instrument }}</strong></td>
                    <td>
                        @if ($trade->direction == 'long')
                            <span class="badge long">▲ Long</span>
                        @else
                            <span class="badge short">▼ Short</span>
                        @endif
                    </td>
                    <td class="number">{{ $trade->quantity }}</td>
                    <td class="number">{{ number_format($trade->entry_price, 2) }}</td>
                    <td class="number">{{ number_format($trade->exit_price, 2) }}</td>
                    <td class="number {{ $trade->pnl >= 0 ? 'green' : 'red' }}">{{ number_format($trade->pnl, 2) }} $</td>
                    <td class="notes">{{ $trade->notes }}</td>
                    <td class="actions">
                        <a href="{{ route('trades.edit', $trade) }}">Edit</a>
                        <button class="link-button" onclick="deleteTrade({{ $trade->id }})">Delete</button>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

@endif
