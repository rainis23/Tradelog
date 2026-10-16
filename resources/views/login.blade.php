@extends('layout')

@section('title', 'Log in')

@section('content')

    <div class="card auth-box">
        <h1>Log in</h1>
        <p class="muted">Demo: demo@tradelog.test / password</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" autofocus>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password">
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn">Log in</button>
        </form>

        <p>No account yet? <a href="{{ route('register') }}">Register</a></p>
    </div>

@endsection
