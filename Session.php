<?php

namespace Gi_BaseFramework;

class Session {

	private static function start()
	{

		if (!session_id()){
			session_start();
		}

		if (!isset($_SESSION[config('app.key')])){

			$_SESSION[config('app.key')] = [];
		}
	}

	public static function set($session_name, $value = false)
	{

		self::start();

		if (is_array($session_name)){
			
			foreach ($session_name as $key => $val){

				self::set($key, $val);
			}

		} else {

			$_SESSION[config('app.key')][$session_name] = $value;
		}
	}

	public static function get($session_name = false)
	{

		self::start();
		
		$session = $_SESSION[config('app.key')];

		if ($session_name){

			$keys = explode('.', $session_name);

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

	public static function destroy()
	{

		self::start();
		
		unset($_SESSION[config('app.key')]);
	}
}