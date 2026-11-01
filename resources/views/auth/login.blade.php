@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
    <div class="card card-body">
        <form class="signin" action="{{ route('login.store') }}" method="POST">
            @csrf
            <h1 class="text-center h1">Sign in</h1>
            <p class="text-center">Sign in to access all site features</p>

            @if ($errors->any())
                <div class="color-red" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="signin__body">
                <div class="form-group">
                    <input type="text" name="login_name" value="{{ old('login_name') }}" placeholder="Username or email" aria-label="Username or email" autocomplete="username" required>
                </div>
                <div class="form-group view-pass">
                    <input type="password" name="password" data-pass-form="login" placeholder="Password" aria-label="Password" autocomplete="current-password" required>
                    <button type="button" class="view-pass-btn" data-pass-btn="login" aria-label="Show password" title="Show password" aria-pressed="false"></button>
                </div>
                <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    <span>Remember me</span>
                </label>
                <div class="signin__submit">
                    <button type="submit" class="btn btn-primary btn-lg btn-full">Sign in</button>
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-lg btn-full">Create account</a>
                </div>
            </div>
        </form>
    </div>
@endsection
