@include('errors._page', [
    'code' => '502',
    'heading' => 'Sunucu şu anda yanıt veremiyor.',
    'description' => 'OgraFi ile sunucu arasındaki bağlantıda geçici bir sorun oluştu. Birkaç saniye sonra yeniden deneyebilirsin.',
    'primaryLabel' => 'Yeniden dene',
    'primaryUrl' => request()->fullUrl(),
    'secondaryLabel' => 'Ana sayfa',
    'secondaryUrl' => url('/'),
])
