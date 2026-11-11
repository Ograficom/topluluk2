<x-filament-panels::page>
    <style>
        .mail-settings{display:grid;gap:20px;width:100%;max-width:1080px;margin-inline:auto}
        .ms-hero{display:flex;align-items:center;gap:16px;padding:24px;border:1px solid #e5e7eb;border-radius:16px;background:#fff}
        .ms-hero-icon{display:flex;align-items:center;justify-content:center;width:50px;height:50px;flex:0 0 50px;border-radius:13px;background:#eff6ff;color:#2563eb}
        .ms-hero-icon svg{width:25px;height:25px}
        .ms-title{margin:0;color:#111827;font-size:17px;font-weight:750}
        .ms-desc{margin:5px 0 0;color:#6b7280;font-size:13px;line-height:1.7}
        .ms-status{display:inline-flex;align-items:center;gap:7px;margin-left:auto;padding:7px 11px;border-radius:999px;background:#f3f4f6;color:#4b5563;font-size:12px;font-weight:650;white-space:nowrap}
        .ms-status-dot{width:7px;height:7px;border-radius:50%;background:#9ca3af}
        .ms-status.is-on{background:#ecfdf5;color:#047857}.ms-status.is-on .ms-status-dot{background:#10b981}
        .ms-card{overflow:hidden;border:1px solid #e5e7eb;border-radius:15px;background:#fff}
        .ms-card-head{padding:19px 23px;border-bottom:1px solid #f0f1f3}
        .ms-card-head h3{margin:0;color:#111827;font-size:15px;font-weight:700}
        .ms-card-head p{margin:5px 0 0;color:#6b7280;font-size:12px;line-height:1.6}
        .ms-card-body{padding:22px}
        .ms-actions{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 22px;border-top:1px solid #f0f1f3;background:#fafafa}
        .ms-actions p{margin:0;color:#6b7280;font-size:12px}
        .ms-test{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:end;gap:14px}
        .ms-note{display:flex;gap:11px;padding:15px 17px;border:1px solid #bfdbfe;border-radius:12px;background:#eff6ff;color:#1e40af}
        .ms-note svg{width:19px;height:19px;flex:0 0 19px;margin-top:2px}
        .ms-note strong{display:block;margin-bottom:3px;font-size:12px}
        .ms-note p{margin:0;color:#1e40af;font-size:12px;line-height:1.7}
        @media(max-width:640px){.ms-hero{align-items:flex-start;padding:17px}.ms-status{margin-left:auto}.ms-card-head{padding:17px}.ms-card-body{padding:16px}.ms-actions{align-items:stretch;flex-direction:column;padding:16px}.ms-test{grid-template-columns:1fr}.ms-test button{width:100%}}
        .dark .ms-hero,.dark .ms-card{background:#18181b;border-color:#3f3f46}
        .dark .ms-title,.dark .ms-card-head h3{color:#fafafa}
        .dark .ms-desc,.dark .ms-card-head p,.dark .ms-actions p{color:#a1a1aa}
        .dark .ms-card-head,.dark .ms-actions{border-color:#3f3f46}
        .dark .ms-actions{background:#202024}
        .dark .ms-note{background:#172554;border-color:#1d4ed8}
        .dark .ms-note p{color:#bfdbfe}
    </style>

    <div class="mail-settings">

<section class="ms-card">
            <header class="ms-card-head">
                <h3>SMTP yapılandırması</h3>
                <p>Bağlantı ve gönderici bilgilerini düzenleyin. Şifreniz güvenli biçimde şifrelenerek saklanır.</p>
            </header>
            <form wire:submit="save">
                <div class="ms-card-body">{{ $this->form }}</div>
                <footer class="ms-actions">
                    <p>Kaydettiğiniz ayarlar yeni e-posta gönderimlerinde kullanılır.</p>
                    <x-filament::button type="submit" icon="heroicon-o-check">Ayarları kaydet</x-filament::button>
                </footer>
            </form>
        </section>

        <section class="ms-card">
            <header class="ms-card-head">
                <h3>SMTP bağlantısını test et</h3>
                <p>Kaydedilmiş SMTP ayarlarını kullanarak kendinize test e-postası gönderin.</p>
            </header>
            <div class="ms-card-body">
                <form wire:submit="sendTest" class="ms-test">
                    <div>
                        <label for="testRecipient" class="fi-fo-field-wrp-label"><span class="text-sm font-medium">Test alıcı e-posta adresi</span></label>
                        <input id="testRecipient" type="email" wire:model="testRecipient" placeholder="ornek@eposta.com" required class="fi-input-wrp w-full rounded-lg border-gray-300 shadow-sm">
                    </div>
                    <x-filament::button type="submit" color="gray" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled" wire:target="sendTest">
                        <span wire:loading.remove wire:target="sendTest">Test e-postası gönder</span>
                        <span wire:loading wire:target="sendTest">Gönderiliyor...</span>
                    </x-filament::button>
                </form>
            </div>
        </section>

        <aside class="ms-note">
            <x-filament::icon icon="heroicon-o-shield-check" />
            <div>
                <strong>Güvenlik ve çalışma şekli</strong>
                <p>SMTP şifresi veritabanında Laravel şifrelemesiyle saklanır ve form açıldığında geri gösterilmez. Şifre alanını boş bırakarak mevcut şifreyi koruyabilirsiniz. SMTP kapatıldığında e-postalar log kanalına yönlendirilir.</p>
            </div>
        </aside>
    </div>
</x-filament-panels::page>
