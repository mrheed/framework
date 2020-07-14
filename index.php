<?php

// permission for write an exp default controller, config, view etc for the first time
define('PERMISSION', 0755);

// one folder for one controller
define('OFFOC', true);
// if value is true, each controllers must be placed in one folder
// true exp: /App/home/HomeController.php, /App/Profil/ProfilController.php, etc
// false exp: /App/HomeController.php, /App/ProfilController.php, etc


// load all helpers
$helpers = scandir(__DIR__ . '/helpers');
foreach ($helpers as $file) {
	
	if ('.' != $file and '..' != $file) {

		require __DIR__ . "/helpers/$file";
	}
}

// autoload
spl_autoload_register(function($class){

	$file = base_dir('/' . str_replace('\\', '/', $class) . '.php');
	
	if (file_exists($file)) {

		require $file;
	}
});

// generate default htaccess
if (!file_exists(base_dir('.htaccess'))) {

	file_put_contents(base_dir('.htaccess'),
		stub(__DIR__ . '/stubs/htaccess.stub'));
}
// generate default index file
if (!file_exists(base_dir('index.php'))) {

	file_put_contents(base_dir('index.php'),
		stub(__DIR__ . '/stubs/index.stub'));
}
// generate default gitignore file
if (!file_exists(base_dir('.gitignore'))) {

	file_put_contents(base_dir('.gitignore'),
		stub(__DIR__ . '/stubs/gitignore.stub'));
}

// load local config (developer setting)
Gi\Env::file(__DIR__ . '/../.env');
// load server config directory
Gi\Config::load(__DIR__ . '/../config');

// error handler and log
Gi\ErrorHandler::register();

date_default_timezone_set(config('app.timezone'));

// get url
$url = Gi\Request::url();

// indexurl / first open app
if ('/' == $url) $url = config('app.indexurl');

// response
(new Gi\Router)->handle($url);