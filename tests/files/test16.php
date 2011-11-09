<?php 
/**
----------------------------------------------------------------------+
* @desc 	Issue 2
* @flag		I2	L14
* @flag		I2	L27
* @score	10.00
----------------------------------------------------------------------+
*/
defined('SYSPATH') or die('No direct script access.');

echo "Reachable";

file_exists('somefile') || exit();

echo "Reachable";

some_random_function() OR die();

echo "Reachable";

some_random_function() 
OR 
/* Some comment */
die();

file_exists('somefile') || die;