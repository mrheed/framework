<?php

namespace Gi\Console;

use Symfony\Component\Console\Application;

class Command
{
    protected $application;

    /**
     * Command constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Register semua commad disini
     *
     * @throws \Exception
     */
    public function register()
    {
        $this->application->add(new MakeCommand('make:controller'));
        $this->application->run();
    }

    // nanti ditambah apa terserah

}