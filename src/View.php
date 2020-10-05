<?php

namespace Gi;

use Gi\Traits\ViewCompiler;

class View {

	use ViewCompiler;

	private
		$path = null,
		$name = null,
		$data = [];

	public function path($path){

		$this->path = $path;

		return $this;
	}

	public function name($name = null){

		$this->name = $name;

		return $this;
	}

	public function data($key = null, $val = null){
		
		if (is_null($key)) {
			return new Collection($this->data);
		}

		if (is_array($key)) {

			foreach ($key as $sub_key => $sub_val){

				$this->data[$sub_key] = $sub_val;
			}

		} elseif(!is_null($val)) {

			$this->data[$key] = $val;
		}

		return $this;
	}

	public function render(){

		extract($this->data);
		
		$file = "$this->path/$this->name.php";

		include $this->getCompiled($file);
	}
}