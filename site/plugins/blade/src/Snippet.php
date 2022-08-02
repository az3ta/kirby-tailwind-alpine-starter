<?php

namespace Leitsch\Blade;

use Illuminate\Support\Facades\View;
use Kirby\Cms\App as Kirby;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Tpl;

class Snippet
{
    public function __construct(protected Kirby $kirby, protected $name, protected array $data = [])
    {
    }

    public function load(): string
    {
        $snippets = A::wrap($this->name);
        $file = null;

        foreach ($snippets as $name) {
            $file = $this->getFile($name);

            if ($file) {
                break;
            }
        }

        if (str_ends_with($file ?? '', Template::EXTENSION_BLADE)) {
            // blade snippet
            return View::file($file, $this->data)->render();
        }

        // vanilla PHP snippet
        return Tpl::load($file, $this->data);
    }

    public function getFile(string $name): ?string
    {
        $bladeFile = $this->kirby->root('snippets') . '/' . $name . '.' . Template::EXTENSION_BLADE;
        $fallbackFile = $this->kirby->root('snippets') . '/' . $name . '.' . Template::EXTENSION_FALLBACK;

        // blade snippet exists
        if (file_exists($bladeFile)) {
            return $bladeFile;
        }

        // vanilla PHP snippets exists
        if (file_exists($fallbackFile)) {
            return $fallbackFile;
        }

        // look for snippet from plugin
        return $this->kirby->extensions('snippets')[$name] ?? null;
    }
}
