// Minimal helpers used across Blade views (no framework needed).
document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-open-modal]');
    if (opener) {
        const id = opener.getAttribute('data-open-modal');
        document.getElementById(id)?.classList.remove('hidden');
    }
    const closer = e.target.closest('[data-close-modal]');
    if (closer) {
        const id = closer.getAttribute('data-close-modal');
        document.getElementById(id)?.classList.add('hidden');
    }

    // Mobile nav drawer: same pattern as modals, but also toggles the
    // matching `<id>-overlay` element so the backdrop shows/hides with it.
    const menuOpener = e.target.closest('[data-open-menu]');
    if (menuOpener) {
        const id = menuOpener.getAttribute('data-open-menu');
        document.getElementById(id)?.classList.remove('hidden');
        document.getElementById(`${id}-overlay`)?.classList.remove('hidden');
    }
    const menuCloser = e.target.closest('[data-close-menu]');
    if (menuCloser) {
        const id = menuCloser.getAttribute('data-close-menu');
        document.getElementById(id)?.classList.add('hidden');
        document.getElementById(`${id}-overlay`)?.classList.add('hidden');
    }
});

// Close any open menu on Escape.
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[id$="-nav"]:not(.hidden)').forEach((el) => {
        el.classList.add('hidden');
        document.getElementById(`${el.id}-overlay`)?.classList.add('hidden');
    });
});