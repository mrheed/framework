<?php

use Ez\View;


function view($file = null, $data = [])
{

	if (is_null($file) and empty($data)) {

		return new View;
	}

	return View::render($file, $data);
}

function registerCss($css)
{

	View::registerCss($css);
}

function registerJs($js)
{
	View::registerJs($js);
}

function layout($layout)
{
	View::layout($layout);
}

function css()
{

	return View::css();
}

function js()
{

	return View::js();
}

function content()
{

	return View::content();
}
