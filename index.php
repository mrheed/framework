<?php

// load all helpers
$helpers = scandir(__DIR__ . '/helpers');
foreach ($helpers as $file) {
	
	if ('.' != $file and '..' != $file) {

		require __DIR__ . "/helpers/$file";
	}
}
