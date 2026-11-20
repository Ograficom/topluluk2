@include('errors._page', [
    'code' => '404',
    'heading' => 'Aradığın sayfa burada değil.',
    'description' => 'Bu bağlantı kaldırılmış, adresi değişmiş ya da yanlış yazılmış olabilir. Dilersen OgraFi ana sayfasından devam edebilirsin.',
    'primaryLabel' => 'Ana sayfaya dön',
    'primaryUrl' => url('/'),
    'secondaryLabel' => 'Geri dön',
    'secondaryUrl' => url()->previous(),
])
