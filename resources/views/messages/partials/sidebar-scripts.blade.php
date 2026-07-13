@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-message-sidebar]').forEach((sidebar) => {
                const searchInput = sidebar.querySelector('[data-message-search]');
                const filterButtons = Array.from(sidebar.querySelectorAll('[data-message-filter]'));
                const items = Array.from(sidebar.querySelectorAll('[data-thread-item]'));
                const emptyState = sidebar.querySelector('[data-thread-empty]');

                let activeFilter = 'all';

                const syncList = () => {
                    const query = (searchInput?.value || '').trim().toLowerCase();
                    let visibleCount = 0;

                    items.forEach((item) => {
                        const haystack = [
                            item.dataset.threadName || '',
                            item.dataset.threadUsername || '',
                            item.dataset.threadSnippet || '',
                        ].join(' ').toLowerCase();

                        const matchesQuery = query === '' || haystack.includes(query);
                        const matchesFilter = activeFilter === 'all'
                            || (activeFilter === 'unread' && item.dataset.threadUnread === '1')
                            || (activeFilter === 'pinned' && item.dataset.threadPinned === '1');

                        const isVisible = matchesQuery && matchesFilter;
                        item.classList.toggle('hidden', !isVisible);
                        if (isVisible) {
                            visibleCount += 1;
                        }
                    });

                    if (emptyState) {
                        emptyState.classList.toggle('hidden', visibleCount > 0);
                    }
                };

                searchInput?.addEventListener('input', syncList);

                filterButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        activeFilter = button.dataset.messageFilter || 'all';
                        filterButtons.forEach((item) => item.classList.toggle('is-active', item === button));
                        syncList();
                    });
                });

                syncList();
            });

            const setupModal = (modalSelector, openerSelector, closerSelector) => {
                const modal = document.querySelector(modalSelector);
                if (!modal) return;

                const openers = document.querySelectorAll(openerSelector);
                const closers = modal.querySelectorAll(closerSelector);

                const show = () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.setAttribute('aria-hidden', 'false');
                    document.documentElement.style.overflow = 'hidden';
                };

                const hide = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.setAttribute('aria-hidden', 'true');
                    document.documentElement.style.overflow = '';
                };

                openers.forEach((button) => button.addEventListener('click', show));
                closers.forEach((button) => button.addEventListener('click', hide));
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        hide();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        hide();
                    }
                });
            };

            setupModal('[data-message-contacts-modal]', '[data-message-contacts-open]', '[data-message-contacts-close]');
        });
    </script>
@endonce
