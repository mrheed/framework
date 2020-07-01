<?php

namespace Ez;

use ReflectionMethod;
use Exception;

class Router {

	private function getDependency($object, $method){
		$reflection = new ReflectionMethod($object, $method);
				
		$parameters = $reflection->getParameters();
		
		$dependencys = [];
		
		foreach ($parameters as $parameter){
			
			if (method_exists($parameter, 'hasType') and $parameter->hasType()){
				
				$dependency = '\\' . $parameter->getType()->getName();
				// $dependency = '\\' . $parameter->getType()->__toString();

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

	private function prepare($url){

		$arr_url = explode('/',  ltrim($url, '/') );

		$is_api = false;

		if ('api' == $arr_url[0]){

			$is_api = true;
			$arr_url = array_except(0, $arr_url);
		}

		$page_name = array_pop($arr_url);
		
		$http_method = strtolower(Request::method());
		
		if (Request::isAjax()) $http_method =  'ajax_'. $http_method;

		$method = camel_case($http_method . '_' . $page_name);

		$class_name = studly_case(
			empty($arr_url) ? $page_name : array_pop($arr_url)
		) . 'Controller';

		$namespace = '\\' . implode('\\',
			array_map(function($val){

				return  studly_case(ucfirst(strtolower($val)));
			
			}, $arr_url)
		);

		$path = base_dir(ltrim(str_replace('\\', '/', $namespace), '/'));

		// pertamakali running dibuatkan contoh dulu
		if (config('app.indexurl') == $url){
			
			$file = "$path/$class_name.php";

			if (!file_exists($file)) {

				mkdir($path, 0777, true);

				file_put_contents($file, stub(
					__DIR__ . '/stubs/controller.stub', [
						'namespace' => $namespace,
						'class_name' => $class_name,
						'method_name' => $method
					]
				));

				mkdir("$path/view", 0777, true);
				file_put_contents("$path/view/$page_name.html",
					stub(__DIR__ . '/stubs/view.stub'));
				
				mkdir("$path/css", 0777, true);
				file_put_contents("$path/css/$page_name.css",
					stub(__DIR__ . '/stubs/css.stub'));
				
				mkdir("$path/js", 0777, true);
				file_put_contents("$path/js/$page_name.js",
					stub(__DIR__ . '/stubs/js.stub'));
			}
		}

		$class = "$namespace\\$class_name";

		if (class_exists($class)){

			$controller = new $class;

			if(method_exists($controller, $method)){
				
				$args = $this->getdependency($controller, $method);
				
				$result = call_user_func_array([$controller, $method], $args);

				if ($result instanceof View) {

					if ($is_api) {
						
						$result = $result->getData()->toArray();

					} else {

						$result = $result->path("$path/view")->render();
					}
				}

				return $result;
			
			} elseif(!$is_api and file_exists("$path/view/$page_name.php")){

				return (new View)->path("$path/view")->name($page_name);
			}

			abort(404, "Controller $class tidak memiliki method $method.");
		}

		abort(404, "Controller $class tidak ditemukan.");
	}

	public function handle($url){

		$result = $this->prepare($url);

		if (is_array($result)){

			header('content-type:application/json');
			$result = json_encode($result);
		}

		echo $result;
	}
}