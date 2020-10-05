<?php

namespace Gi\Drivers;

use Gi\Contracts\Database;

class Mysql implements Database
{
	public function connect($host, $name, $user, $pass)
	{
        return mysqli_connect($host, $user, $pass, $name);
	}

	public function close($handler)
	{
		return mysqli_close($handler);
	}

	public function query($handler, $query)
	{
		return mysqli_query($handler, $query);
	}

	public function fetch($result, $type = false)
	{
		if($type == 'object')
			return mysqli_fetch_object($result);
		else
			return mysqli_fetch_assoc($result);
	}

	public function free($result)
	{
		return mysqli_free_result($result);
	}

	public function escape($string)
	{
		
		return mysql_real_escape_string($string);
		// return mysqli_escape_string($string);
	}
}