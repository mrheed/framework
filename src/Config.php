<?php

namespace Gi;

class Config {

    public static $config = [];

	public static function load($dir)
	{
		if (is_dir($dir)){

			$scan = scandir($dir);
			
			foreach ($scan as $file_name){
				
				$config_file = $dir.'/'.$file_name;	
				
				if(is_file($config_file)){
					
					$key = str_replace('.php', null, $file_name);
					$value = require $config_file;
					self::set($key, $value);
				}	
			}

		} else {

			mkdir($dir, PERMISSION, true);

			file_put_contents($dir . '/app.php',
				stub(__DIR__ . '/stubs/app_config.stub'));
			
			file_put_contents($dir . '/database.php',
				stub(__DIR__ . '/stubs/db_config.stub'));

			self::load($dir);
		}
	}

	public static function set($key, $value = null){

		if (is_array($key)) {

			foreach ($key as $subkey => $subvalue) {

				self::set($subkey, $subvalue);
			}

		} else {

			static::$config[$key] = $value;
		}
	}

	public static function get($key = null){

		$config = static::$config;
		
		if (!is_null($key)){

			$keys = explode('.', $key);
			foreach ($keys as $key){
			
				if (array_key_exists($key, $config)){
			
					$config = $config[$key];

				} else {
			
					return null;
				}
			}	
		}

		return $config;
	}
}