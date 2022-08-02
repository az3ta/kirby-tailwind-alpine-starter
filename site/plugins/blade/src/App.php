<?php

namespace Leitsch\Blade;

use Illuminate\Container\Container;

class App extends Container
{
    /**
     * @return string
     *
     * @see \Illuminate\Contracts\Foundation\Application::getNamespace()
     */
    public function getNamespace(): string
    {
        return 'App\\';
    }
}
