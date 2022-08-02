<?php

use Kirby\Cms\App as Kirby;
use Leitsch\Blade\BladeDirectives;
use Leitsch\Blade\BladeFactory;
use Leitsch\Blade\BladeIfStatements;
use Leitsch\Blade\Paths;
use Leitsch\Blade\Snippet;
use Leitsch\Blade\Template;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('leitsch/blade', [
    'options' => [
        'views' => function () {
            return kirby()->roots()->cache() . '/views';
        },
        'directives' => [],
        'ifs' => [],
    ],
    'components' => [
        'template' => function (Kirby $kirby, string $name, string $contentType = null) {
            return new Template($kirby, $name, $contentType);
        },
        'snippet' => function (Kirby $kirby, $name, array $data = []): ?string {
            return (new Snippet($kirby, $name, $data))->load();
        },
    ],
    'hooks' => [
        'system.loadPlugins:after' => function () {
            BladeFactory::register([Paths::getPathTemplates()], Paths::getPathViews());
            BladeDirectives::register();
            BladeIfStatements::register();
        },
    ],
    'routes' => [
        [
            // Block all requests to /url.blade and return 404
            'pattern' => '(:all)\.blade',
            'action' => function ($all) {
                return false;
            },
        ],
    ],
]);
