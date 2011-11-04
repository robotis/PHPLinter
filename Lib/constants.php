<?php
/**
----------------------------------------------------------------------+
*  @desc			Defined constants
*  @file 			constants.php
*  @author 			JÃ³hann T. MarÃ­usson <jtm@hi.is>
*  @since 		    Jun 14, 2010
*  @package 		phplinter
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
*
----------------------------------------------------------------------+
*/
namespace PHPLinter;
// OPTIONS
define('OPT_NO_CONVENTION',				0x00001);
define('OPT_NO_WARNING',				0x00002);
define('OPT_NO_REFACTOR',				0x00004);
define('OPT_NO_ERROR',					0x00008);
define('OPT_VERBOSE',					0x00010);
define('OPT_DEBUG',						0x00020);
define('OPT_SCORE_ONLY',				0x00040);
define('OPT_INFORMATION',				0x00080);
define('OPT_HTML_REPORT',				0x00100);
define('OPT_FIND_FUNC',					0x00200);
define('OPT_NO_DEPRICATED',				0x00400);
define('OPT_DEBUG_EXTRA',				0x00800);
define('OPT_NO_SECURITY',				0x01000);
define('OPT_ONLY_SECURITY',				0x02000);
define('OPT_OVERWRITE_REPORT',			0x04000);
define('OPT_USE_COLOR',					0x08000);
define('OPT_DEBUG_TIME',				0x10000);
define('OPT_DEBUG_TIME_EXTRA',			0x20000);
define('OPT_QUIET',						0x40000);

// SCORE
define('SCORE_FULL',					10.0);
define('I_PENALTY',						0);
define('C_PENALTY',						0.01);
define('W_PENALTY',						0.3);
define('D_PENALTY',						0.3);
define('R_PENALTY',						0.8);
define('E_PENALTY',						1.0);
define('S_PENALTY',						1.0);

define('T_IGNORE',					0);
define('T_NEWLINE',					1000);
define('T_CURLY_CLOSE', 			1001);
define('T_SQUARE_OPEN', 			1002);
define('T_SQUARE_CLOSE', 			1003);
define('T_PARENTHESIS_OPEN', 		1004);
define('T_PARENTHESIS_CLOSE', 		1005);
define('T_COLON',					1006);
define('T_SEMICOLON',				1007);
define('T_EQUALS',					1008);
define('T_STR_CONCAT',				1009);
define('T_TRUE',					1010);
define('T_FALSE',					1011);
define('T_NULL',					1012);
define('T_THEN',					1013);
define('T_NOT',						1014);
define('T_METHOD', 					1015);
define('T_SELF', 					1016);
define('T_PARENT', 					1017);
define('T_BACKTICK', 				1018);
define('T_ANON_FUNCTION', 			1019);
