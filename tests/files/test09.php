<?php
/**
----------------------------------------------------------------------+
*  @desc	Security
*  @flag	S2	L18
*  @flag	I2	L20
*  @flag	I2	L21
*  @flag	I2	L22
*  @flag	S1	L22
*  @flag	I2	L23
*  @flag	I2	L24
*  @flag	I2	L25
*  @flag	S3	L25
*  @flag	S1	L26
*  @score	-30.00
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
preg_match("/{$_GET['breach']}/e", $p, $m);