<?php

function array_except($key_except, array $array){

	$key_excepts = (array) $key_except;

	foreach ($key_excepts as $val){

		if (isset($array[$val])){

			unset($array[$val]);
		}
	}

	return $array;
}