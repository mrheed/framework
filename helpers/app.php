<?php

use Gi\Request;
use Gi\Validation;
use Gi\Session;
use Gi\Database;
use Gi\Config;

function env($key, $default = null){

	return Gi\Env::get($key, $default);
}

function config($key = null, $value = null){

	if (is_null($value)){

		return Config::get($key);

	} else {

		Config::set($key, $value);
	}
}

function session($key = null, $value = null){
	
	if (is_null($value)){

		return Session::get($key);

	} else {

		Session::set($key, $value);
	}
}

function db($connect = null){

	if (!is_null($connect)) {

		Database::connect($connect);
	}

	return new Database;
}

function table($table){

	return (new Database)->table($table);
}

function destroy_session(){

	Session::destroy();
}

function abort($code = 500, $message = 'Error Processing Request'){

	throw new Exception($message, $code);
	exit;	
}

function dd(){

	$args = func_get_args();

	echo "
			<!DOCTYPE HTML>
			<html>
				<head>
					<title>" . config('app.name') . " [DEBUG]</title>
				</head>
				<body style='background-color: #3a3a3a;color: #bdbdbd;'>";

	foreach ($args as $arg){
		echo "		<pre>";
		// var_dump($arg);
		print_r($arg);
		echo "		</pre>";
	}

	echo "
				</body>
			</html>
		";

	exit;
}

function url($append = null){

	return (new Request)->base() . '/' . ltrim($append, '/');
}

function redirect($append = null){

	$url = url($append);

	header("location: $url");
	exit;
}


function write_log($msg, $filename){

	$log_file = base_dir($filename);

	$log = fopen($log_file, 'a+');
	fwrite($log, date('Y/m/d H:i:s').' | '.
		trim(preg_replace('/\s\s+/', ' ',  $msg))."\n");
	
	fclose($log);
}

function client_ip(){

	//Just get the headers if we can or else use the SERVER global
	if(function_exists('apache_request_headers')){

		$headers = apache_request_headers();

	} else {

		$headers = $_SERVER;

	}

	//Get the forwarded IP if it exists
	if (array_key_exists('X-Forwarded-For', $headers ) and
		filter_var($headers['X-Forwarded-For'],
			FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4)){

		$the_ip = $headers['X-Forwarded-For'];

	} elseif(array_key_exists('HTTP_X_FORWARDED_FOR', $headers) and
		filter_var($headers['HTTP_X_FORWARDED_FOR'],
			FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )){

		$the_ip = $headers['HTTP_X_FORWARDED_FOR'];

	} else {
		
		$the_ip = filter_var($_SERVER['REMOTE_ADDR'],
			FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);

	}

	return $the_ip;
}


function client_device(){

	return isset($_SERVER['HTTP_USER_AGENT']) ?
		$_SERVER['HTTP_USER_AGENT'] :
		'Unknown';
}

function base_dir($foo = null){

	return realpath(__DIR__ . '/../../') . "/$foo";
}

function stub($file, $data = []){

	$stub = file_get_contents($file);

	foreach ($data as $key => $val) {
		$stub = str_replace('{' . $key . '}', $val, $stub);
	}

	return $stub;
}

function view($name, $data = []){

	return (new Gi\View)->name($name)->data($data);
}

function request(){

	return New Request;
}

function post($name = null){

	return Request::data($name);
}

function get($name = null){

	return Request::query($name);
}

function post_rules($rules = []){

	$data = post()->toArray();
	return (new Validation)->rules($rules)->data($data)->validate();
}

function get_rules($rules){

	$data = get()->toArray();
	return (new Validation)->rules($rules)->data($data)->validate();
}