<?php

namespace Ez;

use ReflectionMethod;
use Exception;

class Router{

	private function getDependency($object, $method){
		$reflection = new ReflectionMethod($object, $method);
				
		$parameters = $reflection->getParameters();
		
		$dependencys = [];
		
		foreach ($parameters as $parameter){
			
			if (method_exists($parameter, 'hasType') and $parameter->hasType()){
				
					
				$dependency = '\\' . $parameter->getType()->__toString();

				if (class_exists($dependency)){
					
					array_push($dependencys, new $dependency);
				
				} else {

					die("Objek $dependency tidak ditemukan.");
				}

			} else {

				die("
					Parameter $$parameter->name tidak diperbolehkan.
					Gunakan dependency injection hanya dalam php7.
				");
			}
		}

		return $dependencys;
	}

	private function call($class, $method){
		if (class_exists($class)){
	
			$controller = new $class;

			if(method_exists($controller, $method)){

				
				$arguments = $this->getdependency($controller, $method);
				
				return call_user_func_array([$controller, $method], $arguments);
			}


			abort(404, "Controller $class tidak memiliki method $method.");
		}

		abort(404, "Controller $class tidak ditemukan.");
	}

	private function extractUrl($url){

		$namespace = explode('/',  ltrim($url, '/') );
		
		$arr_pisah_method = array_splice($namespace, (count($namespace) - 1));
		$str_ambil_method =
			strtolower(Request::method()).'_'.$arr_pisah_method[0];
		
		if (Request::isAjax()){

			$str_ambil_method =  'ajax_'. $str_ambil_method;
		}
		
		$method = camel_case($str_ambil_method);

		$arr_pisah_class = array_splice($namespace, (count($namespace) - 1));
		
		if (isset($arr_pisah_class[0])){

			$class = studly_case($arr_pisah_class[0]);
		
		} else {

			$class = studly_case($arr_pisah_method[0]);
		}

		if (empty($namespace)){
			
			$class =  '\\' . $class . 'Controller';

		} else {

			$namespace = array_map(function($val){

				return  studly_case(ucfirst(strtolower($val)));
				
			}, $namespace);

			$class = '\\' . implode('\\', $namespace) . "\\" . $class  . 'Controller';
		}

		return compact('class', 'method');
	}

	public function start($default_url){
		
		$url = Request::url();

		if ('/' == $url) $url = $default_url;

		extract($this->extractUrl($url));


		// cek default index url
		if (config('app.indexurl') == $url){


			$controller = base_dir(ltrim(str_replace('\\', '/', $class), '/') . '.php');

			// cek default controoler
			if (!file_exists($controller)) {
				
				// generate default controoler

				$namespace = explode('\\', $class);
				
				// get class name of controller
				$class_name = array_pop($namespace);

				// get namespace
				$namespace = ltrim(implode('\\', $namespace), '\\');

				$controller_stub = stub(__DIR__ . '/stubs/controller.stub', [
					'namespace' => $namespace,
					'class_name' => $class_name,
					'method_name' => $method
				]);


				$arr_controller_path = explode('/', $controller);
				array_pop($arr_controller_path);
				$controller_path = implode('/', $arr_controller_path);

				mkdir($controller_path, 0777, true);

				file_put_contents($controller, $controller_stub);
			}
		}

		$result = $this->call($class, $method);
	
		if (is_array($result)){

			header('content-type:application/json');
			die(json_encode($result));
		}

		return $result;
	}
}