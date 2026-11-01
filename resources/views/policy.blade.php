@extends('layouts.repost')

@section('title', $title . ' — OgraFi')
@section('content')
    <article class="card card-body repost-static-page"><h1>{{ $title }}</h1><div class="repost-detail-content">{!! $policy !!}</div></article>
@endsection
