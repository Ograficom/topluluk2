<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<meta name="theme-color" content="#2563eb">
<link rel="manifest" href="/manifest.webmanifest">
<title>Hızlı not — OgraFi</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f7f9fd;color:#172033;font:16px system-ui,sans-serif}main{max-width:680px;margin:auto;padding:24px}a{color:#1769f5}section{background:white;padding:24px;border:1px solid #e2e8f0;border-radius:18px;margin-top:20px}textarea{width:100%;min-height:260px;padding:12px;border:1px solid #cbd5e1;border-radius:12px;font:inherit;resize:vertical}button{background:#1769f5;color:white;border:0;border-radius:10px;padding:10px 18px;font:inherit;cursor:pointer}button.secondary{background:#e2e8f0;color:#172033}.actions{display:flex;gap:8px;flex-wrap:wrap}p{color:#64748b;line-height:1.5}
</style>
</head>
<body><main>
<a href="/">← OgraFi</a>
<section><h1>Hızlı not</h1>
<p>Notun sadece bu cihazdaki tarayıcıda saklanır. Hesabına aktarılmaz; ortak cihazlarda özel bilgi yazma.</p>
<label for="note">Notun</label>
<textarea id="note" maxlength="20000" placeholder="Aklındakileri yaz..."></textarea>
<div class="actions"><button type="button" id="save">Notu kaydet</button><button type="button" class="secondary" id="clear">Notu sil</button></div>
<p id="status" role="status"></p></section>
</main>
<script>
(() => {
 const key = 'ografi:pwa:local-note:v1';
 const note = document.getElementById('note'), status = document.getElementById('status');
 try { note.value = localStorage.getItem(key) || ''; } catch (_) {}
 document.getElementById('save').addEventListener('click', () => {
   try { localStorage.setItem(key, note.value); status.textContent = 'Not bu cihazda kaydedildi.'; }
   catch (_) { status.textContent = 'Not kaydedilemedi. Tarayıcı depolamasını kontrol et.'; }
 });
 document.getElementById('clear').addEventListener('click', () => {
   if (!confirm('Bu cihazdaki not silinsin mi?')) return;
   try { localStorage.removeItem(key); note.value = ''; status.textContent = 'Not silindi.'; }
   catch (_) { status.textContent = 'Not silinemedi.'; }
 });
})();
</script>
</body></html>
