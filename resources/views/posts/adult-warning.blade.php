@extends('layouts.app')

@section('title', '18+ content')

@section('content')
    <section class="card card-body">
        <h1 class="h3">18+ content</h1>
        <p class="color-gray">This post is restricted. Enable 18+ content in your account settings to view it.</p>
        @auth
            <a class="btn btn-primary" href="{{ route('settings.edit', ['tab' => 'site']) }}">Open content settings</a>
        @else
            <a class="btn btn-primary" href="{{ route('login') }}">Sign in to update your content settings</a>
        @endauth
    </section>
@endsection
