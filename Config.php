<?php

namespace Ez;

class Config {

    public static $config = [];

	public static function load($dir)
	{
		if (is_dir($dir)){

			$scan = scandir($dir);
			
			foreach ($scan as $file_name){
				
				$config_file = $dir.'/'.$file_name;	
				
				if(is_file($config_file)){
					
					$config_name = str_replace('.php', null, $file_name);
					$config_value = require $config_file;
					self::set($config_name, $config_value);
				}	
			}

		} else {

			mkdir($dir, 0777, true);

			file_put_contents($dir . '/app.php',
				stub(__DIR__ . '/stubs/app_config.stub'));
			
			file_put_contents($dir . '/database.php',
				stub(__DIR__ . '/stubs/db_config.stub'));

			self::load($dir);
		}
	}

	public static function set($config_name, $config_value){

		static::$config[$config_name] = $config_value;
	}

	public static function get($config_name = null)
	{

		$config = static::$config;
		
		if ($config_name){

			$keys = explode('.', $config_name);
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