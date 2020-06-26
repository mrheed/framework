<?php

$helpers = scandir(__DIR__ . '/helpers');

foreach ($helpers as $file) {
	
	if ('.' != $file and '..' != $file) {

		require __DIR__ . "/helpers/$file";
	}
}

spl_autoload_register(function($class){

	$file = base_dir('/' . str_replace('\\', '/', $class) . '.php');
	
	if (file_exists($file)) {

		require $file;
	}
});

if (!file_exists(base_dir('.htaccess'))) {

	file_put_contents(base_dir('.htaccess'),
		stub(__DIR__ . '/stubs/htaccess.stub'));
}

if (!file_exists(base_dir('index.php'))) {

	file_put_contents(base_dir('index.php'),
		stub(__DIR__ . '/stubs/index.stub'));
}


if (!file_exists(base_dir('.gitignore'))) {

	file_put_contents(base_dir('.gitignore'),
		stub(__DIR__ . '/stubs/gitignore.stub'));
}

Ez\Env::file(__DIR__ . '/../.env');
Ez\Config::load(__DIR__ . '/../config');

Ez\ErrorHandler::register();

date_default_timezone_set(config('app.timezone'));

(new Ez\Router)->start(config('app.indexurl'));