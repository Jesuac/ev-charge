<?php

/**
 * Resolve a URL served out of the public directory to its path on disk.
 */
function publicFileFor(string $url): string
{
    return public_path(ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/'));
}

test('the manifest is served with the web app manifest content type', function () {
    $this->get(route('manifest'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/manifest+json');
});

test('the manifest describes an installable app', function () {
    $this->get(route('manifest'))
        ->assertJsonPath('name', config('app.name'))
        ->assertJsonPath('short_name', 'Charger')
        ->assertJsonPath('display', 'standalone')
        ->assertJsonPath('start_url', route('charges.index'))
        ->assertJsonPath('scope', url('/'))
        ->assertJsonCount(4, 'icons')
        ->assertJsonCount(2, 'shortcuts');
});

test('every icon the manifest advertises exists', function () {
    $icons = $this->get(route('manifest'))->json('icons');

    expect($icons)->toHaveCount(4);

    foreach ($icons as $icon) {
        expect(publicFileFor($icon['src']))->toBeFile();
    }

    expect(collect($icons)->pluck('purpose')->all())
        ->toBe(['any', 'any', 'maskable', 'maskable']);
});

test('the layout wires up the manifest and the install meta tags', function () {
    $response = $this->get(route('charges.index'))->assertOk();

    $response->assertSee('<link rel="manifest" href="'.route('manifest').'">', escape: false);
    $response->assertSee('<meta name="theme-color" content="#fafafa" media="(prefers-color-scheme: light)">', escape: false);
    $response->assertSee('<meta name="theme-color" content="#09090b" media="(prefers-color-scheme: dark)">', escape: false);
    $response->assertSee('rel="apple-touch-icon"', escape: false);
    $response->assertSee('<meta name="apple-mobile-web-app-capable" content="yes">', escape: false);
});

test('the service worker and its offline fallback ship in the public directory', function () {
    expect(public_path('sw.js'))->toBeFile()
        ->and(public_path('offline.html'))->toBeFile();
});

test('every url the service worker precaches exists', function () {
    $worker = file_get_contents(public_path('sw.js'));

    /** Only the constants at the top of the file name real files. */
    $constants = str($worker)->before('self.addEventListener');

    preg_match_all("#'(/[^']*)'#", $constants, $urls);

    expect($urls[1])->toContain('/offline.html');

    foreach ($urls[1] as $url) {
        expect(publicFileFor($url))->toBeFile();
    }
});
