<?php

namespace Ez;

class View {

	private static $path = null;
	private static $name = null;
	private static $data = [];

	public function path($path){

		if (!is_null($path)) {

			static::$path = $path;
		}

		return $this;
	}

	public function name($name = null){

		if (!is_null($name)) {

			static::$name = $name;
		}

		return $this;
	}

	public function data($key = null, $val = null){
		
		if (is_array($key)) {

			foreach ($key as $sub_key => $sub_val){

				static::$data[$sub_key] = $sub_val;
			}

		} elseif(!is_null($val)) {

			static::$data[$key] = $val;
		}

		return $this;
	}

	public function getData(){

		return new Collection(static::$data);
	}

	public function render(){

		$file = static::$path . '/' . static::$name . '.php';

		dd(fileatime($file));
		exit();

		return new Collection([
			'data' => static::$data,
			'file' => $file
		]);
	}
}