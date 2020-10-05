<?php

namespace Gi;

class Controller {

	public function before($class, $method, $args){

		return [$class, $method, $args];
	}

	public function after($class, $method, $args, $result){

		return $result;
	}
}