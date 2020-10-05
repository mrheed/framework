<?php

namespace Gi\Drivers;

use Gi\Contracts\Database;

class Postgre implements Database
{
	public function connect($host, $name, $user, $pass)
	{
        return pg_connect(
        	"host=$host port=5432 dbname=$name user=$user password=$pass"
        );
	}

	public function close($handler)
	{
		return pg_close($handler);
	}

	public function query($handler, $query)
	{
		return pg_query($handler, $query);
	}

	public function fetch($result, $type = false)
	{
		if($type == 'object')
			return pg_fetch_object($result);
		else
			return pg_fetch_assoc($result);
	}

	public function free($result)
	{
		return pg_free_result($result);
	}

	public function escape($string)
	{

		return pg_escape_string($string);
	}

}