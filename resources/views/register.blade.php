@extends('layout')

@section('title', 'Register')

@section('content')

    <div class="card auth-box">
        <h1>Register</h1>
        <br>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" autofocus>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password (at least 8 characters)</label>
                <input type="password" name="password" id="password">
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Repeat password</label>
                <input type="password" name="password_confirmation" id="password_confirmation">
            </div>

            <button type="submit" class="btn">Register</button>
        </form>

        <p>Already registered? <a href="{{ route('login') }}">Log in</a></p>
    </div>

@endsection
