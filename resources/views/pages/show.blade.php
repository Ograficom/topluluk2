@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <div class="space-y-4">
        <section class="space-y-4">
            <div class="alma-panel p-5 sm:p-6">
                <h1 class="alma-page-title">{{ $page->title }}</h1>
            </div>
            <div class="alma-panel p-5 sm:p-6">
            <div class="prose prose-slate max-w-none dark:prose-invert">
                {!! $page->content !!}
            </div>
            </div>
        </section>
    </div>
@endsection



