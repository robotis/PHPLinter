<?php
/**
----------------------------------------------------------------------+
*  @desc			Global variables
*  @file 			globals.php
*  @author 			Jóhann T. Maríusson <jtm@robot.is>
*  @since 		    Feb 15, 2011
*  @package 		PHPLinter
*  @copyright     
*    phplinter is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
----------------------------------------------------------------------+
*/
return array(
	'$GLOBALS', 				//References all variables available in global scope
	'$_SERVER', 				//Server and execution environment information
	'$_GET', 					//HTTP GET variables
	'$_POST', 					//HTTP POST variables
	'$_FILES', 					//HTTP File Upload variables
	'$_REQUEST', 				//HTTP Request variables
	'$_SESSION', 				//Session variables
	'$_ENV', 					//Environment variables
	'$_COOKIE', 				//HTTP Cookies
	'$php_errormsg', 			//The previous error message
	'$HTTP_RAW_POST_DATA', 		//Raw POST data
	'$http_response_header', 	//HTTP response headers
	'$argc', 					//The number of arguments passed to script
	'$argv', 					//Array of arguments passed to script
);