<?php

use Ez\Database;


function db($connect = null)
{

	if (!is_null($connect)) {

		Database::connect($connect);
	}

	return new Database;
}

function table($table)
{

	return (new Database)->table($table);
}