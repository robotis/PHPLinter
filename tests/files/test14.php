<?php
/**
----------------------------------------------------------------------+
* @desc 	File level scope
* @score	10.00
----------------------------------------------------------------------+
*/
if(true) {
    if (!defined('NOTDEFINED')) {
	if ( strstr( PHP_OS, 'WIN') ) {
	    if( empty($_SERVER['TEMP']) ) {
		die("...");
	    }
	    else {
		DEFINE('CACHE_DIR', $_SERVER['TEMP'] . '/');
	    }
	} else {
	    DEFINE('CACHE_DIR','/tmp');
	}
    }
}
elseif( !defined('CACHE_DIR') ) {
    DEFINE('CACHE_DIR', '');
}