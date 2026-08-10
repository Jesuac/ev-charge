<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    /**
     * Serve the web app manifest that makes the charger log installable.
     */
    public function __invoke(): JsonResponse
    {
        return response()
            ->json([
                'name' => config('app.name'),
                'short_name' => 'Charger',
                'description' => 'Log and split the charging sessions on the building\'s shared EV charger.',
                'start_url' => route('charges.index'),
                'scope' => url('/'),
                'display' => 'standalone',
                'background_color' => '#fafafa',
                'theme_color' => '#fafafa',
                'icons' => [
                    [
                        'src' => asset('icons/icon-192.png'),
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ],
                    [
                        'src' => asset('icons/icon-512.png'),
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ],
                    [
                        'src' => asset('icons/icon-maskable-192.png'),
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'purpose' => 'maskable',
                    ],
                    [
                        'src' => asset('icons/icon-maskable-512.png'),
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'maskable',
                    ],
                ],
                'shortcuts' => [
                    [
                        'name' => 'Add charge',
                        'url' => route('charges.create'),
                    ],
                    [
                        'name' => 'Report',
                        'url' => route('report.index'),
                    ],
                ],
            ], options: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ->header('Content-Type', 'application/manifest+json');
    }
}
