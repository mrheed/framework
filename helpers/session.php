<?php

use Ez\Session;

function session($session_name = false, $val = false)
{

	if (false == $session_name){


		return Session::get();

	} else if (false == $val){

		return Session::get($session_name);

	} else {

		Session::set($session_name, $val);
	}
}

function destroy_session()
{

	Session::destroy();
}