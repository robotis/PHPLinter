<?php
/**
----------------------------------------------------------------------+
*  @desc			Test 09
----------------------------------------------------------------------+
*/
require($_GET['breach']);
$test = 'Some command';
proc_open($test);
pcntl_exec($test);
shell_exec($_GET['breach']);
assert($test);
array_walk($test, $_GET['breach']);
array_walk($_GET['breach'], $test);
`{$_GET['breach']}`;