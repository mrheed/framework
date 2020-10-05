<?php

namespace Gi\Contracts;

interface Database {

	public function connect($host, $database, $username, $password);
	public function close($db_handler);
	public function query($db_hanlder, $query);
	public function fetch($result, $type);
	public function escape($string);
}