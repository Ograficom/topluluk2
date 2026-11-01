@extends('layouts.repost')

@section('title', $page->meta_title ?: $page->title . ' — OgraFi')
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->content), 155))
@section('canonical_url', url()->current())
@section('content')
    <article class="card card-body repost-static-page">
        <span class="repost-eyebrow">OGRAFI</span>
        <h1>{{ $page->title }}</h1>
        <div class="repost-detail-content">{!! $page->content !!}</div>
    </article>
@endsection
