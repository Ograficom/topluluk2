@extends('layouts.app')

@section('title', $page->title)

@push('head')
<style>
    .page-title-identity {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 38px;
        padding: 3px 17px;
        border: 1px solid #d9dde3;
        border-radius: 18px;
        background: #ffffff;
        color: #050505;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        box-sizing: border-box;
        box-shadow: none;
    }

    html.dark .page-title-identity,
    .dark .page-title-identity {
        border-color: #27272a;
        background: #18181b;
        color: #fafafa;
    }

    @media (max-width: 640px) {
        .page-title-identity {
            width: 100vw;
            min-height: 34px;
            margin-right: calc(50% - 50vw);
            margin-left: calc(50% - 50vw);
            padding: 2px 14px;
            border-right: 0;
            border-left: 0;
            border-radius: 16px;
            font-size: 13px;
        }
    }
</style>
@endpush

@section('content')
    <div class="space-y-4">
        <section class="space-y-4">
            <h1 class="page-title-identity">{{ $page->title }}</h1>
            <div class="alma-panel p-5 sm:p-6">
            <div class="prose prose-slate max-w-none dark:prose-invert">
                {!! $page->content !!}
            </div>
            </div>
        </section>
    </div>
@endsection



