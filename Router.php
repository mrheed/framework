<?php

namespace Gi;

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
		
		if (Request::isAjax()) {

			$is_api = true;
		}

		$method = camel_case($http_method . '_' . $page_name);

		$class_name = studly_case(
			empty($arr_url) ? $page_name : array_pop($arr_url)
		);

		if (true == OFFOC) array_push($arr_url, $class_name);

		$class_name .= 'Controller';

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

				mkdir($path, PERMISSION, true);

				file_put_contents($file, stub(
					__DIR__ . '/stubs/controller.stub', [
						'namespace' => ltrim($namespace, '\\'),
						'class_name' => $class_name,
						'method_name' => $method
					]
				));

				mkdir("$path/view", PERMISSION, true);
				file_put_contents("$path/view/$page_name.php",
					stub(__DIR__ . '/stubs/view.stub'));
				file_put_contents("$path/view/layout.php",
					stub(__DIR__ . '/stubs/view_layout.stub'));
				
				mkdir("$path/css", PERMISSION, true);
				file_put_contents("$path/css/$page_name.css",
					stub(__DIR__ . '/stubs/css.stub'));
				
				mkdir("$path/js", PERMISSION, true);
				file_put_contents("$path/js/$page_name.js",
					stub(__DIR__ . '/stubs/js.stub'));
			}
		}

		$class = "$namespace\\$class_name";

		if (class_exists($class)){

			$controller = new $class;

			if(method_exists($controller, $method)){
				
				$args = $this->getdependency($controller, $method);
				
				if (method_exists($controller, 'before')){

					list($class, $method, $args) = $controller->before($class, $method, $args);
					$controller = new $class;
				}

				$result = call_user_func_array([$controller, $method], $args);

				if (method_exists($controller, 'after')) {

					$result = $controller->after($class, $method, $args, $result);
				}

				if ($result instanceof View) {

					if ($is_api) {
						
						$result = $result->data()->toArray();

					} else {

						$result = $result->path("$path/view");
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
		
		if($result instanceof View) {

			$result->render();

		} elseif($result instanceof Collection){

			header('content-type:application/json');
			echo json_encode($result->toArray());

		} elseif(is_array($result)){

			header('content-type:application/json');
			echo json_encode($result);
		}

		exit;
	}
}