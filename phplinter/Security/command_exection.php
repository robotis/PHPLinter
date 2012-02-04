<?php
return array(
	'exec', // - Executes a specified command and returns the last line of the programs output
	'passthru', // - Executes a specified command and returns all of the output directly to the remote browser
	'system', // - Much the same as passthru() but doesn't handle binary data
	'popen', // - Executes a specified command and connects its output or input stream to a PHP file descriptor
	'proc_open',
	'pcntl_exec',
	'shell_exec',
	'assert',
	'create_function',
	'include',
	'include_once',
	'require',
	'require_once',
	// $_GET['func_name']($_GET['param']) - Variable Function names, $_GET['func_name'] could be the value "exec" and then `exec()` would be called. 
);