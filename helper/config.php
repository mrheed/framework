<?php

use Ez\Env;
use Ez\Config;

function env($key, $default = null){

	return Env::get($key, $default);
}

function config($config_name = null){

	return Config::get($config_name);
}