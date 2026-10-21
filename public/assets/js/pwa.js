if ('serviceWorker' in navigator && window.isSecureContext) {
    navigator.serviceWorker.register(BASE_URL + '/service-worker.js', { scope: BASE_URL + '/' }).catch(() => {
        // Installation is optional; the normal website remains usable.
    });
}

let installPrompt;
window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    installPrompt = event;
    const button = document.getElementById('install-journal');
    if (button) button.hidden = false;
});
window.addEventListener('appinstalled', () => {
    installPrompt = null;
    const button = document.getElementById('install-journal');
    if (button) button.hidden = true;
});
document.getElementById('install-journal')?.addEventListener('click', async () => {
    if (!installPrompt) return;
    const prompt = installPrompt;
    installPrompt = null;
    document.getElementById('install-journal').hidden = true;
    await prompt.prompt();
});
