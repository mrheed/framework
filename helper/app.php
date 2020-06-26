<?php

use Ez\ErrorHandler;
use Ez\Router;
use Ez\Request;
use Ez\Validation;

function abort($code = 500, $message = 'Error Processing Request'){

	throw new Exception($message, $code);
	exit;	
}

function dd($foo){

	echo "
			<!DOCTYPE HTML>
			<html>
				<head>
					<title>" . config('app.name') . " [DEBUG]</title>
				</head>
				<body style='background-color: #3a3a3a;color: #bdbdbd;'>";

	var_dump($foo);

	echo		"</body>
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