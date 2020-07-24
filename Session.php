<?php

namespace Gi;

class Session {

	private static function key(){

		return config('app.name') . config('app.key');
	}

	private static function start(){

		if (!session_id()){
			session_start();
		}

		if (!isset($_SESSION[self::key()])){

			$_SESSION[self::key()] = [];
		}
	}

	public static function set($key, $value = null){

		self::start();

		if (is_array($key)){
			
			foreach ($key as $subkey => $val){

				self::set($subkey, $val);
			}

		} else {

			$_SESSION[self::key()][$key] = $value;
		}
	}

	public static function get($key = null){

		self::start();

		$session = $_SESSION[self::key()];

		if (!is_null($key)){

			$keys = explode('.', $key);

			foreach ($keys as $key){
			
				if (array_key_exists($key, $session)){
			
					$session = $session[$key];

				} else {
			
					return null;
				}
			}
		}

		return $session;
	}

	public static function destroy(){

		self::start();
		
		unset($_SESSION[self::key()]);
	}
}