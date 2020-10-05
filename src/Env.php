<?php

namespace Gi;

use Exception;

class Env {

	private static $env_file;

	public static function file($env_file){

		static::$env_file = $env_file;
	}

	public static function get($key, $default = null){

		if (file_exists(static::$env_file)){

			$arr_env = file(static::$env_file);

			$env = [];

			foreach ($arr_env as $str){
				
				if ($str != '' && mb_strpos($str, '=') !== false){

					list($env_key, $env_val) = explode('=', $str);
					
					$env[ trim($env_key) ] = trim($env_val);
				}
			}
			
			if (key_exists($key, $env)) return $env[$key];
		}
		
		static::write($key, $default);

		return $default;
	}

	private static function write($key, $val){
		
		$write_env = fopen(static::$env_file, 'a+');
		fwrite($write_env, $key . '=' . $val . "\n");
		fclose($write_env);
	}
}