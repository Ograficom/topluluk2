@extends('layouts.app')

@section('title', 'Gonderi Raporu')
@section('hide_feed_header')
@endsection
@section('no_container_padding')
@endsection

@push('head')
<style>
    .post-report-page {
        position: fixed !important;
        inset: 0 !important;
        z-index: 2147483000 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100vw !important;
        height: 100vh !important;
        overflow-y: auto !important;
        background: rgba(0, 0, 0, 0.82) !important;
        backdrop-filter: blur(14px) saturate(140%) !important;
        -webkit-backdrop-filter: blur(14px) saturate(140%) !important;
        padding: 24px 16px;
        box-sizing: border-box;
    }

    .post-report-shell {
        width: 100%;
        max-width: 32rem;
        margin: auto;
    }

    .post-report-card {
        border-radius: 18px;
        background: #ffffff;
        padding: 22px;
    }

    .post-report-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .post-report-title {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.3;
    }

    .post-report-close {
        display: inline-flex;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 999px;
        background: #f1f1f1;
        color: #6b6b6b;
        text-decoration: none;
        font-size: 22px;
        line-height: 1;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .post-report-close:hover,
    .post-report-close:focus-visible {
        background: #e4e4e4;
        color: #111111;
    }

    .post-report-form {
        margin-top: 18px;
    }

    .post-report-field + .post-report-field {
        margin-top: 14px;
    }

    .post-report-label {
        display: block;
        margin-bottom: 8px;
        color: #111827;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
    }

    .post-report-select,
    .post-report-textarea {
        width: 100%;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        background: #ffffff;
        color: #111827;
        font-size: 14px;
        line-height: 1.5;
    }

    .post-report-select {
        min-height: 46px;
        padding: 0 14px;
    }

    .post-report-textarea {
        min-height: 150px;
        padding: 14px;
        resize: vertical;
    }

    .post-report-error {
        margin-top: 6px;
        color: #dc2626;
        font-size: 12px;
        line-height: 1.4;
    }

    .post-report-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .post-report-submit {
        display: inline-flex;
        min-width: 120px;
        height: 44px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 14px;
        background: #8fb2ff;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
    }

    @media (max-width: 640px) {
        .post-report-page {
            padding: 12px;
        }

        .post-report-card {
            padding: 18px;
        }
    }
</style>
@endpush

@section('content')
    <div class="post-report-page">
        <div class="post-report-shell">
            <section class="post-report-card">
                <div class="post-report-header">
                    <h1 class="post-report-title">Rapor</h1>
                    <a href="{{ route('blog.post', $post) }}" class="post-report-close" aria-label="Kapat">×</a>
                </div>

                <form action="{{ route('blog.post.report', $post) }}" method="POST" class="post-report-form">
                    @csrf

                    <div class="post-report-field">
                        <label for="postReportTopic" class="post-report-label">Bir neden secin</label>
                        <select id="postReportTopic" name="topic" class="post-report-select" required>
                            <option value="" disabled {{ old('topic') ? '' : 'selected' }}>Bir neden secin</option>
                            @foreach($topics as $topicValue => $topicLabel)
                                <option value="{{ $topicValue }}" @selected(old('topic') === $topicValue)>{{ $topicLabel }}</option>
                            @endforeach
                        </select>
                        @error('topic')
                            <div class="post-report-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="post-report-field">
                        <label for="postReportMessage" class="post-report-label">Mesaj</label>
                        <textarea id="postReportMessage" name="message" rows="6" class="post-report-textarea" placeholder="Kisa bir aciklama ekle...">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="post-report-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="post-report-actions">
                        <button type="submit" class="post-report-submit">Gondermek</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
