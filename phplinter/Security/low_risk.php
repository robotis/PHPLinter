<?php
return array(
	'extract', 		// Opens the door for register_globals attacks (see study in scarlet).  
	'putenv',
	'ini_set',
	'mail', 		// has CRLF injection in the 3rd parameter,  opens the door for spam. 
	'header', 		// on old systems CRLF injection could be used for xss or other purposes,  now it is still a problem if they do a header("location: ...");  and they don't die();.  The script keeps executing after a call to header(), and will still print output normally. This is nasty if you are trying to protect an administrative area. 
	'pclose',
	'proc_nice',
	'proc_terminate',
	'proc_close',
	'pfsockopen',
	'fsockopen',
	'apache_child_terminate',
	'posix_kill',
	'posix_mkfifo',
	'posix_setpgid',
	'posix_setsid',
	'posix_setuid',
);