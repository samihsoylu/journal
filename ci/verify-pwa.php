<?php

declare(strict_types=1);

namespace Journal\Checks;

function verifyPwaBrowser(EditorBrowser $browser, string $base, string $privateEntryUrl, callable $connection) : void
{
    waitFor(static fn () => $browser->script('return navigator.serviceWorker.ready.then(() => Boolean(navigator.serviceWorker.controller));'), 'PWA service worker control', 20);
    $manifest = $browser->cdp('Page.getAppManifest');
    ensure(($manifest['errors'] ?? []) === [], 'PWA manifest has errors: ' . json_encode($manifest['errors']));
    $errors = $browser->cdp('Page.getInstallabilityErrors')['installabilityErrors'];
    ensure($errors === [], 'PWA is not installable: ' . json_encode($errors));
    $browser->visit($base . '/account');
    $screenshotDirectory = getenv('JOURNAL_SCREENSHOT_DIR');

    if ($screenshotDirectory) {
        ensure(is_dir($screenshotDirectory), 'Screenshot directory is missing');
        $browser->cdp('Emulation.setDeviceMetricsOverride', ['width' => 1280, 'height' => 900, 'deviceScaleFactor' => 1, 'mobile' => false]);
        file_put_contents($screenshotDirectory . '/account-desktop.png', base64_decode($browser->screenshot(), true));
        $browser->cdp('Emulation.setDeviceMetricsOverride', ['width' => 390, 'height' => 844, 'deviceScaleFactor' => 1, 'mobile' => true]);
        file_put_contents($screenshotDirectory . '/account-mobile.png', base64_decode($browser->screenshot(), true));
        $browser->cdp('Emulation.clearDeviceMetricsOverride');
    }

    try {
        // Stop only this rehearsal's owned web container. Page-level network
        // emulation does not reliably cover a separate service-worker target.
        $connection(false);
        $browser->visit($privateEntryUrl);
        ensure($browser->script("return document.body.textContent.includes('Journal needs a connection') && !document.body.textContent.includes('Browser image regression');"), 'Offline PWA exposed a private entry or failed to show the public notice');
        $urls = $browser->script(<<<'JS'
            return caches.keys().then(async names => {
                const urls = [];
                for (const name of names) {
                    const cache = await caches.open(name);
                    for (const request of await cache.keys()) urls.push(request.url);
                }
                return urls;
            });
            JS);
        ensure($urls === [$base . '/offline.html'], 'PWA cached resources other than its public offline notice: ' . json_encode($urls));
    } finally {
        $connection(true);
    }
    $browser->visit($base . '/account');
    ensure($browser->script("return document.title.includes('Account settings');"), 'PWA did not recover when online');
    echo "PWA browser: manifest/installability, controlled navigation, offline notice, no private Cache Storage, and online recovery passed\n";
}
