@include('errors._page', [
    'code' => '419',
    'heading' => 'Oturumunun süresi doldu.',
    'description' => 'Güvenliğin için oturum yenilemesi gerekiyor. Sayfayı yeniden açıp işlemini tekrar deneyebilirsin.',
    'primaryLabel' => 'Ana sayfaya dön',
    'primaryUrl' => url('/'),
    'secondaryLabel' => 'Giriş yap',
    'secondaryUrl' => route('login'),
])
