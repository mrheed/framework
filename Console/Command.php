<?php

namespace Gi\Console;

use Symfony\Component\Console\Application;

class Command extends Application
{
    /**
     * Command constructor.
     * @param string|string $name
     * @param string|string $version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
    }

    /**
     * Register semua commad disini
     *
     * @throws \Exception
     */
    public function registerCommand()
    {
        $this->add(new MakeCommand('make:controller'));
        $this->run();
    }

    // nanti ditambah apa terserah

}