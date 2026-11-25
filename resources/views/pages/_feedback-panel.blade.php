<div id="site-page-feedback" class="modal site-feedback-modal" data-modal="site-page-feedback" data-site-feedback
    data-url="{{ route('pages.feedback.show', $page->slug, false) }}" data-post-url="{{ route('pages.feedback.store', $page->slug, false) }}" aria-hidden="true" inert>
    <div class="modal-content" aria-labelledby="site-feedback-title">
        <div class="modal-header">
            <h2 class="modal-title" id="site-feedback-title">Bu yazı faydalı oldu mu?</h2>
            <button class="modal-close btn btn-sm btn-secondary-ghost btn-circle" type="button" data-modal-close aria-label="Kapat"><x-repost-icon name="close" size="18" /></button>
        </div>
        <div class="modal-body">
    <div class="site-feedback-votes" aria-label="Sayfayı değerlendir">
        <button type="button" class="btn btn-secondary" data-vote="like" aria-pressed="false" disabled>
            <x-repost-icon name="like" size="18" /> Evet <span data-count="like">—</span>
        </button>
        <button type="button" class="btn btn-secondary" data-vote="dislike" aria-pressed="false" disabled>
            <x-repost-icon name="dislike" size="18" /> Hayır <span data-count="dislike">—</span>
        </button>
    </div>
    <form id="site-feedback-form" class="site-feedback-form" data-site-feedback-form>
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
    </form>
    <p class="small site-feedback-status" role="status" aria-live="polite"></p>
    <button class="btn btn-sm btn-secondary" type="button" data-site-feedback-retry hidden>Tekrar dene</button>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" type="submit" form="site-feedback-form" disabled>Gönder</button>
        </div>
    </div>
</div>
