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
});
