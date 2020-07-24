<?php

namespace Gi;

class ErrorHandler{

	private static
		$errors = [
    	    E_DEPRECATED => 'Deprecated',
    	    E_USER_DEPRECATED => 'User Deprecated',
    	    E_ERROR => 'Fatal Error',
    	    E_WARNING => 'Warning',
    	    E_PARSE => 'Parsing Error',
    	    E_NOTICE => 'Notice',
    	    E_CORE_ERROR => 'Core Error',
    	    E_CORE_WARNING => 'Core Warning',
    	    E_COMPILE_ERROR => 'Compile Error',
    	    E_COMPILE_WARNING => 'Compile Warning',
    	    E_USER_ERROR => 'User Error',
    	    E_USER_WARNING => 'User Warning',
    	    E_USER_NOTICE => 'User Notice',
    	    E_STRICT => 'Runtime Notice',
    	    E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
    	];

	public static function display($type, $message, $file, $line){
		
		// if (!empty(ob_get_status())) {
			// ob_clean();
		// }

		write_log(
			client_ip() . " | $type | $message | $file | $line", 'error.log'
		);

		$error = isset(static::$errors[$type]) ? static::$errors[$type] : "ERROR $type";

		if (false === config('app.debug')){
			
			$message =
				'Mohon maaf, sepertinya sedang terjadi kesalahan, ' . 
				'Silahkan tunggu beberapa saat lagi, Terimakasih.';
			
			// if (404 == $type){

				// $message = 'Halaman yang Anda cari tidak ditemukan.';

			// } else if (403 == $type){

				// $message = 'Anda tidak mempunyai akses untuk halaman ini.';
			// }
		}

		if (Request::isAjax()){

			header('content-type: application/json');
			$error_reporting = [
				'error' => $error,
				'message' => $message
			];

			if (true === config('app.debug') or null === config('app.debug')){

				$error_reporting['file'] = $file;
				$error_reporting['line'] = $line;
			}

			echo json_encode($error_reporting);

		} else {


			$html = "
				<!DOCTYPE HTML>
				<html>
					<head>
						<title>" . config('app.name') . " [ERROR]</title>
					</head>
					<body style='background-color: #3a3a3a;color: #bdbdbd;'>
						<h1>$error</h1>
						<hr>
						<h3>$message</h3>";

			if (true === config('app.debug') or null === config('app.debug')){

				$html .= "
						<p>file: <b>$file</b></p>
						<p>line: <b>$line</b></p>
				";
			}

			$html .= "
					</body>
				</html>
			";


			echo $html;
		}

		exit;
	}

	public static function register(){

		error_reporting(E_ALL);
		set_exception_handler(function($exc){

			self::display(
				$exc->getCode(),
				$exc->getMessage(),
				$exc->getFile(),
				$exc->getLine()
			);
		});

		set_error_handler(function($type, $message, $file, $line){

			self::display($type, $message, $file, $line);
		});

		register_shutdown_function(function(){
			
			if($error = error_get_last()){

				extract($error);

				self::display($type, $message, $file, $line);
			}
		});
	}
}