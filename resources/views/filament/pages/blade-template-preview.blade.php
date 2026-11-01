<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blade şablon önizleme</title>
    @vite(['resources/css/app.css', 'resources/css/ografi.css'])
    <style>body{padding:20px;background:#f8fafc}.preview-frame{width:100%;min-height:80vh;border:1px solid #dbe2ea;border-radius:10px;background:#fff}.preview-error{padding:16px;border:1px solid #fecaca;border-radius:8px;background:#fff1f2;color:#991b1b}</style>
</head>
<body>
    <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <strong>Şablon önizleme</strong><span>{{ $template }}</span>
    </header>
    @if($error)
        <div class="preview-error">{{ $error }}</div>
    @else
        <iframe class="preview-frame" title="Blade önizleme" sandbox="allow-same-origin" srcdoc="{{ $content }}"></iframe>
    @endif
</body>
</html>
