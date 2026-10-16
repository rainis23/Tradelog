{{-- ONE form for both NEW trades and EDITING a trade.
     A new trade has no id yet ($trade->id is empty), an existing trade has one. --}}
@extends('layout')

@section('title', $trade->id ? 'Edit trade' : 'New trade')

@section('content')

    <h1>{{ $trade->id ? 'Edit trade' : 'New trade' }}</h1>
    <br>

    <div class="card form-small">
        <form method="POST" action="{{ $trade->id ? route('trades.update', $trade) : route('trades.store') }}">
            @csrf

            {{-- A form can only send GET or POST. @method('PUT') tells Laravel that this is an update. --}}
            @if ($trade->id)
                @method('PUT')
            @endif

            {{-- old('field', default) puts back what you typed if there was a validation error.
                 @error('field') shows the error message of that field. --}}

            <div class="form-row">
                <div class="form-group">
                    <label for="account_id">Account</label>
                    <select name="account_id" id="account_id">
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('account_id', $trade->account_id) == $account->id)>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('account_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="trade_date">Date</label>
                    <input type="date" name="trade_date" id="trade_date" value="{{ old('trade_date', $trade->trade_date->format('Y-m-d')) }}">
                    @error('trade_date')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="instrument">Contract</label>
                    <select name="instrument" id="instrument">
                        <option value="">-- choose --</option>
                        @foreach (App\Models\Trade::POINT_VALUES as $symbol => $pointValue)
                            <option value="{{ $symbol }}" @selected(old('instrument', $trade->instrument) == $symbol)>
                                {{ $symbol }} ({{ $pointValue }} $ per point)
                            </option>
                        @endforeach
                    </select>
                    @error('instrument')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="direction">Direction</label>
                    <select name="direction" id="direction">
                        <option value="long" @selected(old('direction', $trade->direction) == 'long')>Long (buy)</option>
                        <option value="short" @selected(old('direction', $trade->direction) == 'short')>Short (sell)</option>
                    </select>
                    @error('direction')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quantity">Contracts</label>
                    <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $trade->quantity) }}">
                    @error('quantity')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="entry_price">Entry price</label>
                    <input type="number" step="0.01" name="entry_price" id="entry_price" value="{{ old('entry_price', $trade->entry_price) }}">
                    @error('entry_price')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="exit_price">Exit price</label>
                    <input type="number" step="0.01" name="exit_price" id="exit_price" value="{{ old('exit_price', $trade->exit_price) }}">
                    @error('exit_price')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes (optional)</label>
                <textarea name="notes" id="notes" rows="4" placeholder="Why did you take this trade?">{{ old('notes', $trade->notes) }}</textarea>
                @error('notes')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <p class="muted">The profit is calculated automatically: points × point value × contracts.</p>

            <button type="submit" class="btn">Save</button>
            <a href="{{ route('trades.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>

@endsection
