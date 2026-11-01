@extends('layouts.app')

@section('title', 'Create account')

@section('content')
    <form class="card card-body" action="{{ route('register.store') }}" method="POST">
        @csrf
        <header>
            <h1>Create account</h1>
            <p>Create an account to join the community and access all Repost features.</p>
        </header>

        @if ($errors->any())
            <div class="color-red" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <ul class="signup__list">
            <li>
                <input type="email" name="email" id="signup-email" value="{{ old('email') }}" placeholder="Email" aria-label="Email" autocomplete="email" required>
            </li>
            <li class="signup__login">
                <input type="text" name="username" id="signup-username" value="{{ old('username') }}" placeholder="Username" aria-label="Username" autocomplete="username" maxlength="30" spellcheck="false" required>
            </li>
            <li class="view-pass">
                <input type="password" name="password" id="signup-password" placeholder="Password (at least 12 characters)" aria-label="Password" autocomplete="new-password" minlength="12" data-pass-form="signup-pass1" required>
                <button type="button" class="view-pass-btn" data-pass-btn="signup-pass1" aria-label="Show password" title="Show password" aria-pressed="false"></button>
            </li>
            <li class="view-pass">
                <input type="password" name="password_confirmation" id="signup-password-confirm" placeholder="Repeat password" aria-label="Repeat password" autocomplete="new-password" minlength="12" data-pass-form="signup-pass2" required>
                <button type="button" class="view-pass-btn" data-pass-btn="signup-pass2" aria-label="Show password" title="Show password" aria-pressed="false"></button>
            </li>
            <li>
                <div class="checkbox">
                    <label class="form-check-label" for="signup-rules">
                        <input class="form-check-input" type="checkbox" id="signup-rules" name="agree_rules" value="1" @checked(old('agree_rules')) required>
                        <span>I agree to the site rules</span>
                    </label>
                </div>
            </li>
            <li><button class="btn btn-primary" type="submit">Create account</button></li>
        </ul>
    </form>
@endsection
