<?php
/**
----------------------------------------------------------------------+
*  @desc			Default config array
*  @file 			default_config.php
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
return array(
	/*
	 *	Information 
	 */
	'INF_UNDONE' => array(
		'message_extra' => '%s',
		'flag' => 'I1',
	),
	'INF_EVAL_USED' => array(
		'message' => '`eval` used',
		'flag' => 'I2',
	),
	/*
	 *	Conventions 
	 */
	'CON_LINE_LENGTH' => array(
		'message_extras' => 'Line to long %d(%d)',
		'flag' => 'C1',
		'compare' => 85,
	),
	'CON_CLASS_NAME' => array(
		'message_extra' => 'Class name does not match (%s)',
		'flag' => 'C2',
		'compare' => '/^[A-Z][\w_]{2,}$/',
	),
	'CON_FUNCTION_NAME' => array(
		'message_extra' => 'Function name does not match (%s)',
		'flag' => 'C3',
		'compare' => '/^[a-z][\w_]{2,}$/',
	),
	'CON_METHOD_NAME' => array(
		'message_extra' => 'Method name does not match (%s)',
		'flag' => 'C4',
		'compare' => '/^[a-z][\w_]{2,}$/',
	),
	'CON_INTERFACE_NAME' => array(
		'message_extra' => 'Interface name does not match (%s)',
		'flag' => 'C5',
		'compare' => '/^[A-Z][\w_]{2,}$/',
	),
	'CON_NO_VISIBILITY' => array(
		'message' => 'No declared visibility',
		'flag' => 'C6',
	),
	'CON_WS_COMMENTED_CODE' => array(
		'message' => 'Possible code commented out',
		'flag' => 'C7',
		'compare' => '/.*\$.*;/',
	),	
	/*
	 *	Warnings 
	 */
	'WAR_OLD_STYLE_CONSTRUCT' => array(
		'message' => 'Old style constructor',
		'flag' => 'W1',
	),
	'WAR_OLD_STYLE_VARIABLE' => array(
		'message' => 'Old style variable',
		'flag' => 'W2',
	),
	'WAR_UNUSED_ARG' => array(
		'message_extra' => 'Unused argument `%s`',
		'flag' => 'W3',
	),
	'WAR_UNUSED_VAR' => array(
		'message_extra' => 'Unused variable `%s`',
		'flag' => 'W4',
	),
	'WAR_UNUSED_METHOD' => array(
		'message_extra' => 'Unused method `%s`',
		'flag' => 'W5',
	),
	'WAR_UNUSED_FUNCTION' => array(
		'message_extra' => 'Unused function `%s`',
		'flag' => 'W6',
	),
	'WAR_UNUSED_CLASS' => array(
		'message_extra' => 'Unused class `%s`',
		'flag' => 'W7',
	),
	'WAR_EVAL_USED' => array(
		'message' => '`eval` used',
		'flag' => 'W8',
	),
	'WAR_PUBLIC_VAR' => array(
		'message' => 'public variable',
		'flag' => 'W9',
	),
	'WAR_EMPTY_CLASS' => array(
		'message' => 'Empty class',
		'flag' => 'W10',
	),
	'WAR_EMPTY_METHOD' => array(
		'message' => 'Empty method',
		'flag' => 'W11',
	),
	'WAR_EMPTY_FUNCTION' => array(
		'message' => 'Empty function',
		'flag' => 'W12',
	),
	'WAR_EMPTY_INTERFACE' => array(
		'message' => 'Empty function',
		'flag' => 'W13',
	),
	'WAR_UNREACHABLE_CODE' => array(
		'message' => 'Unreachable code',
		'flag' => 'W14',
	),
	'WAR_HACK_MARKED' => array(
		'message' => 'Hack found',
		'flag' => 'W15',
	),
	'WAR_WS_AFTER_CLOSE' => array(
		'message' => 'Whitespace after final close tag',
		'flag' => 'W16',
	),
	/*
	 * Depricated warnings
	 */
	'DPR_DEPRICATED_TOKEN' => array(
		'message_extra' => '`%s` Depricated',
		'flag' => 'D1',
		'compare' => array(T_GLOBAL=>'global')
	),
	'DPR_DEPRICATED_STRING' => array(
		'message_extra' => '`%s` Depricated',
		'flag' => 'D2',
		'compare' => array('eregi', 'eregi_replace', 'ereg', 
			'ereg_replace', 'split', 'spliti')
	),
	/*
	 *	Refactor smells 
	 */
	'REF_METHOD_LENGTH' => array(
		'message_extras' => 'Method to long %d(%d)',
		'flag' => 'R1',
		'compare' => 300,
	),
	'REF_FUNCTION_LENGTH' => array(
		'message_extras' => 'Function to long %d(%d)',
		'flag' => 'R2',
		'compare' => 300
	),
	'REF_CLASS_LENGTH' => array(
		'message_extras' => 'Class to long %d(%d)',
		'flag' => 'R3',
		'compare' => 2500
	),	
	'REF_BRANCHES' => array(
		'message_extras' => 'To many branches %d(%d)',
		'flag' => 'R4',
		'compare' => 12
	),	
	'REF_LOCALS' => array(
		'message_extras' => 'To many local variables %d(%d)',
		'flag' => 'R5',
		'compare' => 24
	),	
	'REF_ARGUMENTS' => array(
		'message_extras' => 'To many arguments %d(%d)',
		'flag' => 'R6',
		'compare' => 5
	),	
	'REF_ANCESTORS' => array(
		'message_extras' => 'To many ancestors %d(%d)',
		'flag' => 'R7',
		'compare' => 7
	),	
	'REF_HTML_MIXIN' => array(
		'message' => 'HTML mixed into code',
		'flag' => 'R8',
	),	
	'REF_HTML_AFTER_CLOSE' => array(
		'message' => 'HTML output after final close tag',
		'flag' => 'R9',
	),
	'REF_UNN_INHERIT' => array(
		'message' => 'Unnecessary inheritance',
		'flag' => 'R10',
	),	
	'REF_NESTED_SWITCH' => array(
		'message' => 'Nested switch',
		'flag' => 'R11',
	),	
	/*
	 *	Errors 
	 */
	'ERR_GLOBAL' => array(
		'message' => '`global` keyword used',
		'flag' => 'E1',
	),
	'ERR_NO_DOCHEAD_FILE' => array(
		'message' => 'File not documented',
		'flag' => 'E2',
	),
	'ERR_NO_DOCHEAD_CLASS' => array(
		'message' => 'Class not documented',
		'flag' => 'E3',
	),
	'ERR_NO_DOCHEAD_METHOD' => array(
		'message' => 'Method not documented',
		'flag' => 'E4',
	),
	'ERR_NO_DOCHEAD_FUNCTION' => array(
		'message' => 'Function not documented',
		'flag' => 'E5',
	),
	'ERR_NO_DOCHEAD_INTERFACE' => array(
		'message' => 'Interface not documented',
		'flag' => 'E6',
	),
	'ERR_NO_DOCHEAD_CLASSVAR' => array(
		'message' => 'Class variable not documented',
		'flag' => 'E7',
	),
);