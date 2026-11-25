<section id="site-page-feedback" class="site-feedback-panel" data-site-feedback
    data-url="{{ route('pages.feedback.show', $page->slug) }}" data-post-url="{{ route('pages.feedback.store', $page->slug) }}" hidden>
    <div class="site-feedback-heading">
        <h2 class="h5">Bu yazı faydalı oldu mu?</h2>
        <button class="btn btn-sm btn-secondary-ghost" type="button" data-site-feedback-close aria-label="Kapat"><x-repost-icon name="close" size="18" /></button>
    </div>
    <div class="site-feedback-votes" aria-label="Sayfayı değerlendir">
        <button type="button" class="btn btn-secondary" data-vote="like" aria-pressed="false" disabled>
            <x-repost-icon name="like" size="18" /> Evet <span data-count="like">—</span>
        </button>
        <button type="button" class="btn btn-secondary" data-vote="dislike" aria-pressed="false" disabled>
            <x-repost-icon name="dislike" size="18" /> Hayır <span data-count="dislike">—</span>
        </button>
    </div>
    <form class="site-feedback-form" data-site-feedback-form>
        @csrf
        <p class="small color-gray">Görüşünüz bu sayfayı geliştirmemize yardımcı olur.</p>
        <div class="form-group">
            <label for="site-feedback-email">E-posta adresiniz <span class="small color-gray">(isteğe bağlı)</span></label>
            <input id="site-feedback-email" type="email" name="email" autocomplete="email" maxlength="254" placeholder="ornek@eposta.com">
        </div>
        <div class="form-group">
            <label for="site-feedback-vote">Değerlendirmeniz</label>
            <select id="site-feedback-vote" name="vote" required>
                <option value="">Seçiniz</option>
                <option value="like">Olumlu – faydalı buldum</option>
                <option value="dislike">Olumsuz – faydalı bulmadım</option>
            </select>
        </div>
        <div class="form-group">
            <label for="site-feedback-message">Geri bildiriminiz</label>
            <textarea id="site-feedback-message" name="message" rows="4" maxlength="3000" placeholder="Bu sayfayla ilgili görüşünüzü yazın..." required></textarea>
        </div>
        <p class="small color-gray">E-posta ve mesajınız yalnızca site yönetimi tarafından görülür. Yeni mesajınız önceki geri bildiriminizi günceller.</p>
        <div class="site-feedback-submit">
            <button class="btn btn-primary" type="submit" disabled>Gönder</button>
        </div>
    </form>
    <p class="small site-feedback-status" role="status" aria-live="polite"></p>
</section>
