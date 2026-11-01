@extends('layouts.repost')

@section('title', 'Reklam ver — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">İŞ ORTAKLIĞI</span><h1>OgraFi'de reklam ver</h1><p>Yerleşimini, süresini ve görselini seç.</p></div></div>
    <form method="POST" action="{{ route('advertise.store') }}" enctype="multipart/form-data" class="card card-body repost-settings-form">
        @csrf
        <label for="placement">Reklam alanı</label>
        <select id="placement" name="placement" required>
            @foreach($placements as $key => $placement)<option value="{{ $key }}" @selected(old('placement') === $key)>{{ $placement['label'] }} — {{ $placement['width'] }}×{{ $placement['height'] }}</option>@endforeach
        </select>
        @foreach($placements as $placement)<p class="meta">{{ $placement['label'] }}: {{ $placement['description'] }}</p>@endforeach
        <label for="duration_days">Yayın süresi</label><select id="duration_days" name="duration_days" required>@foreach($durations as $days => $price)<option value="{{ $days }}" @selected(old('duration_days', 7) == $days)>{{ $days }} gün — {{ number_format($price / 100, 2, ',', '.') }} TL</option>@endforeach</select>
        <label for="title">Reklam başlığı</label><input id="title" name="title" value="{{ old('title') }}" maxlength="80">
        <label for="target_url">Hedef bağlantı</label><input id="target_url" name="target_url" type="url" value="{{ old('target_url') }}">
        <label for="image">Reklam görseli</label><input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
        @error('image')<span class="repost-form-error">{{ $message }}</span>@enderror
        <button class="btn btn-primary btn-rounded" type="submit">Reklam talebi oluştur</button>
    </form>
@endsection
