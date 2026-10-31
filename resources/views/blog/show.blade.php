return;
        }

        const pollOption = event.target.closest('[data-poll-option]');
        if (pollOption) {
            const wrapper = pollOption.closest('[data-poll-block]');
            if (!wrapper || wrapper.dataset.pollSubmitting === '1') {
                return;
            }

            wrapper.dataset.pollSubmitting = '1';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(wrapper.dataset.pollVoteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    block_id: wrapper.dataset.pollId,
                    option_index: Number(pollOption.dataset.pollOption),
                }),
            })
                .then(function (res) { return res.ok ? res.json() : Promise.reject(res); })
                .then(function (data) {
                    const options = Array.from(wrapper.querySelectorAll('[data-poll-option]'));
                    const optionsWrap = wrapper.querySelector('.ps-poll__options');
                    const votedIndex = Number(pollOption.dataset.pollOption);
                    const percentages = data.percentages || {};

                    optionsWrap.innerHTML = options.map(function (opt) {
                        const idx = Number(opt.dataset.pollOption);
                        const pct = percentages[idx] ?? 0;
                        const isVoted = idx === votedIndex;
                        return '<div class="ps-poll__result' + (isVoted ? ' ps-poll__result--voted' : '') + '">'
                            + '<div class="ps-poll__result-row"><span>' + opt.textContent + (isVoted ? ' ✓' : '') + '</span><span>' + pct + '%</span></div>'
                            + '<div class="ps-poll__bar"><div class="ps-poll__bar-fill" style="width:' + pct + '%"></div></div>'
                            + '</div>';
                    }).join('');

                    wrapper.removeAttribute('data-poll-block');
                    const meta = wrapper.querySelector('.ps-poll__meta');
                    if (meta && typeof data.total !== 'undefined') {
                        meta.textContent = data.total + ' oy';
                    }
                })
                .catch(function () {
                    wrapper.dataset.pollSubmitting = '0';
                });
        }
    });
</script>
@endpush

@push('head')
<style>
/* Repost HTML şablonunun Blade gönderi detay sayfasına uyarlanmış yerleşimi.
   Mevcut Laravel gönderi/yorum formları ve veri akışı korunur. */
.repost-detail-page {
  width: min(100% - 32px, 1440px);
  max-width: 1440px;
  margin: 0 auto;
  padding: 24px 0 64px;
}
.repost-detail-grid {
  display: grid !important;
  grid-template-columns: 210px minmax(0, 1fr) 300px;
  align-items: start;
  gap: 24px;
}
.repost-detail-sidebar {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-width: 0;
  min-height: 420px;
  position: sticky;
  top: 88px;
  padding: 16px 12px;
  background: #fff;
  border: 1px solid #e8edf3;
  border-radius: 14px;
}
.repost-detail-nav { list-style: none; padding: 0; margin: 0; display: grid; gap: 5px; }
.repost-detail-nav-link {
  display: flex; align-items: center; gap: 11px; padding: 10px 11px;
  border-radius: 9px; color: #334155; text-decoration: none; font-size: 14px;
}
.repost-detail-nav-link:hover, .repost-detail-nav-link[aria-current="page"] { background: #f1f5f9; color: #1d4ed8; }
.repost-detail-nav-icon { width: 20px; display: inline-flex; justify-content: center; color: #64748b; }
.repost-detail-sidebar .sidebar__title { margin: 0 0 12px; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
.repost-detail-category { display: flex; align-items: center; gap: 10px; color: #334155; text-decoration: none; font-size: 14px; }
.repost-detail-category img, .repost-detail-category-initial { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; display: grid; place-items: center; background: #eff6ff; color: #2563eb; font-weight: 700; }
.repost-detail-sidebar-note { color: #94a3b8; font-size: 12px; }
.repost-detail-feed { min-width: 0; width: 100%; max-width: none !important; }
.repost-detail-speedbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 0 0 12px; color: #94a3b8; font-size: 12px; }
.repost-detail-speedbar a { color: #64748b; text-decoration: none; }
.repost-detail-speedbar a:hover { color: #2563eb; }
.repost-detail-grid .ps-layout { display: block !important; width: 100% !important; max-width: none !important; min-width: 0 !important; margin: 0 !important; padding: 0 !important; }
.repost-detail-grid .ps-main { width: 100% !important; max-width: none !important; flex: 1 1 auto !important; min-width: 0 !important; }
.repost-detail-widgets { min-width: 0; display: flex; flex-direction: column; gap: 14px; position: sticky; top: 88px; }
.repost-detail-widgets .widget { background: #fff; border: 1px solid #e8edf3; border-radius: 14px; box-shadow: none; }
.repost-detail-widgets .widget__title { margin: 0; font-size: 14px; font-weight: 700; color: #334155; }
.repost-detail-widgets .widget__list { list-style: none; margin: 0; padding: 0; }
.repost-detail-widgets .entry-card, .repost-detail-widgets .comm-card { padding: 12px 0; border-bottom: 1px solid #eef2f6; }
.repost-detail-widgets .entry-card:last-child, .repost-detail-widgets .comm-card:last-child { border-bottom: 0; }
.repost-detail-widgets .entry-box { display: flex; gap: 10px; align-items: center; }
.repost-detail-widgets .entry-box-content { min-width: 0; flex: 1; display: grid; gap: 6px; }
.repost-detail-widgets .entry-box-content > a { color: #334155; text-decoration: none; font-size: 13px; font-weight: 600; }
.repost-detail-widgets .entry-box-image img { width: 68px; height: 58px; object-fit: cover; border-radius: 8px; }
.repost-detail-widgets .meta { color: #94a3b8; font-size: 11px; }
.repost-detail-widgets .meta-item { display: inline-flex; align-items: center; gap: 4px; margin-right: 8px; }
.repost-detail-widgets .user-box { display: flex; align-items: center; gap: 8px; }
.repost-detail-widgets .avatar { width: 30px; height: 30px; flex: 0 0 30px; border-radius: 50%; overflow: hidden; display: grid; place-items: center; background: #f1f5f9; color: #64748b; }
.repost-detail-widgets .avatar img { width: 100%; height: 100%; object-fit: cover; }
.repost-detail-widgets .name { color: #334155; font-size: 12px; font-weight: 600; }
.repost-detail-widgets .comm-card-short { margin: 8px 0; color: #475569; font-size: 12px; line-height: 1.5; }
.repost-detail-widgets .small { color: #64748b; font-size: 11px; }
.repost-detail-widgets a.link { color: #2563eb; text-decoration: none; }
@media (max-width: 1100px) {
  .repost-detail-grid { grid-template-columns: 190px minmax(0, 1fr); gap: 16px; }
  .repost-detail-widgets { display: none; }
}
@media (max-width: 760px) {
  .repost-detail-page { width: 100%; padding: 12px 0 48px; }
  .repost-detail-grid { display: block !important; }
