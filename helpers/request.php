<?php

use Ez\Request;
use Ez\Validation;

function request()
{

	return New Request;
}

function post($name = null)
{

	return Request::data($name);
}

function get($name = null)
{

	return Request::query($name);
}

function post_rules($rules)
{

	$data = post()->toArray();
	return (new Validation)->rules($rules)->data($data)->validate();
}

function get_rules($rules)
{

	$data = get()->toArray();
	return (new Validation)->rules($rules)->data($data)->validate();
}