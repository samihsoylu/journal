<?php

declare(strict_types=1);

namespace Journal\Checks;

use RuntimeException;

// Native W3C WebDriver client; Chrome and ChromeDriver are test tools only.
final class EditorBrowser
{
    private string $session = '';

    public function __construct(private readonly string $endpoint) {}

    public function request(string $method, string $path, ?array $body = null) : mixed
    {
        $client = curl_init($this->endpoint . $path);
        curl_setopt_array($client, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 40,
            CURLOPT_PROXY => '',
        ]);

        if ($body !== null) {
            curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));
        }
        $response = curl_exec($client);
        ensure($response !== false, 'WebDriver request failed: ' . curl_error($client));
        $value = json_decode($response, true, flags: JSON_THROW_ON_ERROR)['value'];

        if (curl_getinfo($client, CURLINFO_RESPONSE_CODE) >= 400) {
            throw new RuntimeException('WebDriver: ' . json_encode($value));
        }

        return $value;
    }

    public function start(string $profile) : void
    {
        $options = ['args' => ['--headless=new', '--no-sandbox', '--disable-dev-shm-usage', '--disable-background-networking', '--window-size=1280,1000', '--user-data-dir=' . $profile]];

        if (getenv('CHROME_PATH')) {
            $options['binary'] = getenv('CHROME_PATH');
        }
        $result = $this->request('POST', '/session', ['capabilities' => ['alwaysMatch' => ['browserName' => 'chrome', 'goog:chromeOptions' => $options]]]);
        $this->session = '/session/' . $result['sessionId'];
    }

    public function visit(string $url) : void
    {
        $this->request('POST', $this->session . '/url', ['url' => $url]);
    }

    public function script(string $script, array $arguments = []) : mixed
    {
        return $this->request('POST', $this->session . '/execute/sync', ['script' => $script, 'args' => $arguments]);
    }

    public function sessionCookie() : array
    {
        $cookies = $this->request('GET', $this->session . '/cookie');
        $cookies = array_values(array_filter($cookies, static fn ($cookie) => $cookie['name'] === 'PHPSESSID'));
        ensure(count($cookies) === 1, 'Missing PHP session cookie');

        return $cookies[0];
    }

    public function cookies() : array
    {
        return $this->request('GET', $this->session . '/cookie');
    }

    public function actions(array $actions) : void
    {
        $this->request('POST', $this->session . '/actions', ['actions' => $actions]);
    }

    public function cdp(string $command, array $parameters = []) : mixed
    {
        return $this->request('POST', $this->session . '/goog/cdp/execute', ['cmd' => $command, 'params' => (object) $parameters]);
    }

    public function screenshot() : string
    {
        return $this->request('GET', $this->session . '/screenshot');
    }

    public function close() : void
    {
        if ($this->session !== '') {
            $this->request('DELETE', $this->session);
        }
    }
}

function verifyEditorBrowser(string $base, string $temporary, callable $connection) : void
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $error);
    ensure($socket !== false, 'Could not reserve a ChromeDriver port: ' . $error);
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $port = substr($address, strrpos($address, ':') + 1);
    $log = fopen($temporary . '/chromedriver.log', 'w');
    $process = proc_open([getenv('CHROMEDRIVER_PATH') ?: 'chromedriver', '--port=' . $port, '--allowed-ips=127.0.0.1'], [0 => ['pipe', 'r'], 1 => $log, 2 => $log], $pipes);
    ensure(is_resource($process), 'Could not start ChromeDriver');
    fclose($pipes[0]);
    $browser = new EditorBrowser('http://' . $address);

    try {
        waitFor(static fn () => $browser->request('GET', '/status')['ready'], 'ChromeDriver', 15);
        $browser->start($temporary . '/chrome-profile');
        $browser->visit($base . '/login');
        $anonymousCookie = $browser->sessionCookie();
        ensure($anonymousCookie['httpOnly'] && $anonymousCookie['sameSite'] === 'Strict' && ! $anonymousCookie['secure'], 'HTTP session cookie protections missing');
        $browser->script("document.querySelector('#username').value = 'freshowner'; document.querySelector('#password').value = 'fresh-password'; document.querySelector('form').requestSubmit();");
        waitFor(static fn () => $browser->script("return location.pathname === '/dashboard';"), 'browser login', 15);
        ensure($anonymousCookie['value'] !== $browser->sessionCookie()['value'], 'Login did not rotate the PHP session cookie');
        $encryptionCookies = array_values(array_filter($browser->cookies(), static fn ($cookie) => $cookie['name'] === 'EEK'));
        ensure(count($encryptionCookies) === 1, 'Missing encryption-key cookie after browser login');
        ensure($encryptionCookies[0]['httpOnly'] === true, 'Encryption-key cookie is missing HttpOnly');
        ensure( ! $browser->script("return document.cookie.split(';').some(cookie => cookie.trim().startsWith('EEK='));"), 'Page JavaScript can read the encryption-key cookie');
        echo "Encryption-key cookie browser: HttpOnly stored and document.cookie access blocked\n";
        $browser->visit($base . '/template/create');
        waitFor(static fn () => $browser->script('return !!(window.tinymce && tinymce.activeEditor && tinymce.activeEditor.initialized);'), 'template editor', 20);
        $browser->script(<<<'JS'
            document.querySelector('#template_title').value = 'Sleep default title';
            document.querySelector('#default_title_pattern').value = 'Sleep {date} {time}';
            tinymce.activeEditor.setContent('<p>Sleep template content</p>');
            tinymce.triggerSave();
            window.onbeforeunload = null;
            document.querySelector('#entry_form').requestSubmit();
            JS);
        waitFor(static fn () => $browser->script("return location.pathname === '/templates';"), 'template saved', 15);
        $browser->visit($base . '/entry/create');
        waitFor(static fn () => $browser->script('return !!(window.tinymce && tinymce.activeEditor && tinymce.activeEditor.initialized);'), 'TinyMCE', 20);
        $browser->script(<<<'JS'
            const select = document.querySelector('#template_id_d');
            M.FormSelect.getInstance(select)?.destroy();
            select.value = [...select.options].find(option => option.text === 'Sleep default title').value;
            M.FormSelect.init(select);
            document.querySelector('#entry_date_time').value = '2020-06-15T08:30';
            JS);
        $template = $browser->script("return fetch('/template/' + document.querySelector('#template_id_d').value + '/ajax').then(response => response.json());");
        $browser->script("tinymce.activeEditor.setContent('<p>Before template load</p>'); document.querySelector('#load-template').click();");
        waitFor(static fn () => $browser->script("return document.querySelector('#category_id_d').value === String(arguments[0]) && !tinymce.activeEditor.getContent().includes('Before template load');", [$template['categoryId']]), 'template load and default category', 15);
        ensure($browser->script("return document.querySelector('#entry_title').value;") === 'Sleep 2020-06-15 08:30', 'Template title did not use the selected entry date');
        $browser->script("document.querySelector('#entry_date_time').value = '2020-06-16T09:45'; document.querySelector('#entry_date_time').dispatchEvent(new Event('change')); ");
        waitFor(static fn () => $browser->script("return document.querySelector('#entry_title').value === 'Sleep 2020-06-16 09:45';"), 'default title date change', 15);
        $browser->script("document.querySelector('#entry_title').value = 'My own title'; document.querySelector('#entry_date_time').value = '2020-06-15T08:30'; document.querySelector('#entry_date_time').dispatchEvent(new Event('change')); ");
        ensure($browser->script("return new Promise(resolve => setTimeout(() => resolve(document.querySelector('#entry_title').value === 'My own title'), 100));"), 'Changing the date overwrote a manual title');
        $override = $browser->script(<<<'JS'
            const select = document.querySelector('#category_id_d');
            const option = [...select.options].find(option => option.value !== select.value);
            M.FormSelect.getInstance(select).destroy();
            select.value = option.value;
            select.dispatchEvent(new Event('change', {bubbles: true}));
            M.FormSelect.init(select);
            document.querySelector('#entry_date_time').value = '2020-06-15T08:30';
            return option.text;
            JS);
        // Delay only the upload request so the real local blob preview can load.
        // A clipboard File goes through TinyMCE's own paste and upload handlers.
        $browser->script(<<<'JS'
            const editor = tinymce.activeEditor;
            window.imagePolicyViolations = [];
            const observe = event => {
                if (event.effectiveDirective === 'img-src') window.imagePolicyViolations.push(event.blockedURI);
            };
            document.addEventListener('securitypolicyviolation', observe);
            editor.getDoc().addEventListener('securitypolicyviolation', observe);
            window.pendingImageUploads = [];
            const originalOpen = XMLHttpRequest.prototype.open;
            const originalSend = XMLHttpRequest.prototype.send;
            XMLHttpRequest.prototype.open = function (method, url, ...rest) {
                this.journalImageUpload = String(url).includes('/media/upload');
                return originalOpen.call(this, method, url, ...rest);
            };
            XMLHttpRequest.prototype.send = function (data) {
                if (this.journalImageUpload) window.pendingImageUploads.push(() => originalSend.call(this, data));
                else originalSend.call(this, data);
            };
            const bytes = Uint8Array.from(atob(arguments[0]), c => c.charCodeAt(0));
            const transfer = new DataTransfer();
            transfer.items.add(new File([bytes], 'pasted-pixel.png', {type: 'image/png'}));
            editor.focus();
            editor.getBody().dispatchEvent(new ClipboardEvent('paste', {clipboardData: transfer, bubbles: true, cancelable: true}));
            JS, [PNG]);
        waitFor(static fn () => $browser->script(<<<'JS'
            const image = tinymce.activeEditor.getBody().querySelector('img');
            return !!(image && image.src.startsWith('blob:') && image.complete && image.naturalWidth > 0 && window.pendingImageUploads.length === 1);
            JS), 'pasted image blob preview', 15);
        ensure($browser->script('return window.imagePolicyViolations;') === [], 'CSP blocked the image preview');
        $browser->script('window.pendingImageUploads.splice(0).forEach(send => send());');
        $imageUrl = waitFor(static fn () => $browser->script(<<<'JS'
            const image = tinymce.activeEditor.getBody().querySelector('img');
            return image && new URL(image.src).pathname.startsWith('/media/') && image.complete && image.naturalWidth > 0 ? image.src : false;
            JS), 'uploaded image', 20);
        ensure($browser->script('return window.imagePolicyViolations;') === [], 'CSP blocked the uploaded image');
        $browser->script("document.querySelector('#entry_title').value = 'Browser image regression'; tinymce.triggerSave(); window.onbeforeunload = null; document.querySelector('#entry_form').requestSubmit();");
        waitFor(static fn () => $browser->script('return /^\/entry\/\d+$/.test(location.pathname);'), 'saved entry', 15);
        $browser->visit($browser->script('return location.href;'));
        waitFor(static fn () => $browser->script(<<<'JS'
            const image = [...document.images].find(image => image.src === arguments[0]);
            return !!(image && image.complete && image.naturalWidth > 0);
            JS, [$imageUrl]), 'saved image after reload', 15);
        ensure($browser->script('return document.body.textContent.includes(arguments[0]);', ['Category: ' . $override]), 'Template category could not be overridden');
        $savedUrl = $browser->script('return location.href;');
        $browser->visit($base . '/entries?date_from=Jun%2015,%202020&date_to=Jun%2015,%202020');
        ensure($browser->script("return document.body.textContent.includes('Browser image regression');"), 'Backdated entry missing from its date filter');
        $browser->visit($base . '/entries?date_from=Jun%2016,%202020&date_to=Jun%2016,%202020');
        ensure( ! $browser->script("return document.body.textContent.includes('Browser image regression');"), 'Backdated entry appeared on another date');
        $browser->visit($savedUrl . '/update');
        ensure($browser->script("return document.querySelector('#entry_date_time').value;") === '2020-06-15T08:30', 'Editing lost the entry date');
        $browser->script('window.onbeforeunload = null;');
        $browser->visit($base . '/categories');
        $browser->script("document.querySelector('#sortable').scrollIntoView({block: 'start'}); window.scrollBy(0, -80);");
        $before = $browser->script("return [...document.querySelector('#sortable').children].map(item => item.id);");
        $points = $browser->script(<<<'JS'
            const cards = document.querySelector('#sortable').children;
            const start = cards[0].querySelector('.card-icon').getBoundingClientRect();
            const end = cards[1].getBoundingClientRect();
            return {x: Math.round(start.x + start.width / 2), y: Math.round(start.y + start.height / 2), endY: Math.round(end.bottom - 10)};
            JS);
        $browser->actions([['type' => 'pointer', 'id' => 'mouse', 'parameters' => ['pointerType' => 'mouse'], 'actions' => [
            ['type' => 'pointerMove', 'duration' => 0, 'x' => $points['x'], 'y' => $points['y']],
            ['type' => 'pointerDown', 'button' => 0],
            ['type' => 'pause', 'duration' => 150],
            ['type' => 'pointerMove', 'duration' => 500, 'x' => $points['x'], 'y' => $points['endY']],
            ['type' => 'pause', 'duration' => 200],
            ['type' => 'pointerUp', 'button' => 0],
        ]]]);
        waitFor(static fn () => $browser->script("return [...document.querySelector('#sortable').children].map(item => item.id);") !== $before, 'category drag order', 15);
        $after = $browser->script("return [...document.querySelector('#sortable').children].map(item => item.id);");
        waitFor(static function () use ($browser, $base, $after) : bool {
            $browser->visit($base . '/categories');

            return $browser->script("return [...document.querySelector('#sortable').children].map(item => item.id);") === $after;
        }, 'saved category drag order', 15);
        echo "TinyMCE browser: pasted blob preview, real upload, save, and reload passed\n";
        echo "Browser features: template title patterns/date/manual override, category/override, backdating/date filters, retained edit date, and real category drag/reload passed\n";
        verifyPwaBrowser($browser, $base, $savedUrl, $connection);
        verifyTwoFactorBrowser($browser, $base);
    } finally {
        try {
            $browser->close();
        } finally {
            proc_terminate($process);
            proc_close($process);
            fclose($log);
        }
    }
}
