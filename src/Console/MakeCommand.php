<?php

namespace Gi\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCommand extends SymfonyCommand
{

    /**
     * @var $path
     */
    protected $path;

    /**
     * @var $io
     */
    protected $io;

    /**
     * Controller constructor.
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addArgument('controller', InputArgument::REQUIRED);
    }

    /**
     * Proses eksekusi terjadi
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $controller = $input->getArgument('controller');
        $this->prepareStub($controller);

        return parent::SUCCESS;
    }

    /**
     * Siapkan bahan2 untuk membuat stub
     *
     * @param $controller
     */
    protected function prepareStub($controller)
    {
        $argv = explode('/', $controller);
        $class = array_pop($argv);
        $stub_config = [
            'namespace' => implode('\\', $argv),
            'class_name' => $class,
            'method_name' => 'getIndex'
        ];
        $conf = [
            'controller' => [
                'path' => [''],
                'name' => $stub_config['class_name'] . '.php',
                'opts' => $stub_config
            ],
            'js' => [
                'path' => ['js'],
                'name' => 'main.js',
                'opts' => []
            ],
            'css' => [
                'path' => ['css'],
                'name' => 'style.css',
                'opts' => []
            ],
            'view_layout' => [
                'path' => ['view'],
                'name' => 'layout.php',
                'opts' => []
            ],
            'view' => [
                'path' => ['view'],
                'name' => 'index.php',
                'opts' => []
            ]
        ];

        $this->path = $argv;
        $this->createStub($conf);
    }

    /**
     * Konfigurasi file stub
     *
     * @param array $stubs
     */
    protected function createStub(array $stubs)
    {
        foreach ($stubs as $key => $val) {
            $content = stub(base_dir('stubs/' . $key . '.stub'), $val['opts']);
            $this->writeStub(array_merge($this->path, $val['path']), $val['name'], $content);
            $this->io->section('File ' . $key . ' dengan nama ' . $val['name'] . ' berhasil dibuat</>');
        }

        $this->io->success('Semua file berada di direktori : '.implode('/', $this->path));
    }

    /**
     * Generate file dari stub
     *
     * @param $path
     * @param $file
     * @param $content
     * @return false|int
     */
    protected function writeStub($path, $file, $content)
    {
        $path = $this->prepareDir($path);
        return file_put_contents($path . '/' . $file, $content);
    }

    /**
     * Buat direktori jika belum dibuat
     *
     * @param $path
     * @return string
     */
    protected function prepareDir($path)
    {
        foreach ($path as $key => $value) {
            $tmp = $path;
            $directory = implode('/', array_splice($tmp, 0, $key + 1));
            if (!is_dir($directory)) mkdir($directory);
        }

        return implode('/', $path);
    }

    // nanti ditambah apa terserah
}