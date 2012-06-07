<?php
/**
----------------------------------------------------------------------+
*  Available rules array.
*  @file 			rules.php
*  @author 			Jóhann T. Marí­usson <jtm@robot.is>
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
	'INF_UNSECURE' => array(
		'message_extra' => 'Possible unsecure function: `%s`',
		'flag' => 'I2',
	),
	'INF_WARNING_DISCLOSURE' => array(
		'message_extra' => 'Possible information disclosure in: `%s`',
		'flag' => 'I3',
	),
	'INF_COMPARE' => array(
		'message_extra' => 'Loose comparison operator used',
		'flag' => 'I4',
	),
	'INF_COMPARE' => array(
		'message_extra' => 'Loose comparison operator used',
		'flag' => 'I5',
	),
	'INF_FOUND_WTF' => array(
		'message' => 'WTF found in comment',
		'flag' => 'I6',
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
		'compare_regex' => '/^[A-Z][\w_]{2,}$/',
	),
	'CON_FUNCTION_NAME' => array(
		'message_extra' => 'Function name does not match (%s)',
		'flag' => 'C3',
		'compare_regex' => '/^[a-z][\w_]{2,}$/',
	),
	'CON_METHOD_NAME' => array(
		'message_extra' => 'Method name does not match (%s)',
		'flag' => 'C4',
		'compare_regex' => '/^[a-z][\w_]{2,}$/',
	),
	'CON_INTERFACE_NAME' => array(
		'message_extra' => 'Interface name does not match (%s)',
		'flag' => 'C5',
		'compare_regex' => '/^[A-Z][\w_]{2,}$/',
	),
	'CON_NO_VISIBILITY' => array(
		'message' => 'No declared visibility',
		'flag' => 'C6',
	),
	'CON_WS_COMMENTED_CODE' => array(
		'message' => 'Possible code commented out',
		'flag' => 'C7',
		'compare_regex' => '/\$.*;/u',
	),	
	'CON_MISPLACED_PROPERTY' => array(
		'message_extra' => 'Property after method declaration in class',
		'flag' => 'C8',
	),
	'CON_EMPTY_COMMENT' => array(
		'message' => 'Empty comment',
		'flag' => 'C9',
	),
	'CON_FILE_CLASSES' => array(
		'message_extras' => 'Too many classes defined in file %d(%d)',
		'flag' => 'C10',
		'compare' => 1
	),
	'CON_PROPERTY_DEFINED_IN_METHOD' => array(
		'message_extra' => 'Property defined in method: %s',
		'flag' => 'C11',
	),
	/*
	 * Formatting
	 */
	'FMT_MIXED_SPACES_TABS' => array(
		'message' => 'Spaces mixed with tabs',
		'flag' => 'F1',
	),
	'FMT_ICONSISTANT_INDENT' => array(
		'message' => 'Inconsistant indentation',
		'flag' => 'F2',
	),
	'FMT_OPER_SPACE_BEFORE' => array(
		'message' => 'Operator not preceded by a space',
		'flag' => 'F3',
	),
	'FMT_OPER_SPACE_AFTER' => array(
		'message' => 'Operator not followed by a space',
		'flag' => 'F4',
	),
	'FMT_COMMA_SPACE_AFTER' => array(
		'message' => 'Comma not followed by a space',
		'flag' => 'F5',
	),
	'FMT_MULTIPLE_STATEMENTS_ON_LINE' => array(
		'message' => 'Multiple statements on single line',
		'flag' => 'F6',
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
	'WAR_UNUSED_PROPERTY' => array(
		'message_extra' => 'Unused property `%s`',
		'flag' => 'W5',
	),
	'WAR_UNUSED_METHOD' => array(
		'message_extra' => 'Unused method `%s`',
		'flag' => 'W6',
	),
	'WAR_UNUSED_FUNCTION' => array(
		'message_extra' => 'Unused function `%s`',
		'flag' => 'W7',
	),
	'WAR_UNUSED_CLASS' => array(
		'message_extra' => 'Unused class `%s`',
		'flag' => 'W8',
	),
	'WAR_EVAL_USED' => array(
		'message' => '`eval` used',
		'flag' => 'W9',
	),
	'WAR_PUBLIC_VAR' => array(
		'message' => 'public variable',
		'flag' => 'W10',
	),
	'WAR_EMPTY_CLASS' => array(
		'message' => 'Empty class',
		'flag' => 'W11',
	),
	'WAR_EMPTY_METHOD' => array(
		'message' => 'Empty method',
		'flag' => 'W12',
	),
	'WAR_EMPTY_FUNCTION' => array(
		'message' => 'Empty function',
		'flag' => 'W13',
	),
	'WAR_EMPTY_INTERFACE' => array(
		'message' => 'Empty interface',
		'flag' => 'W14',
	),
	'WAR_UNREACHABLE_CODE' => array(
		'message' => 'Unreachable code',
		'flag' => 'W15',
	),
	'WAR_HACK_MARKED' => array(
		'message' => 'Hack marked',
		'flag' => 'W16',
	),
	'WAR_WS_AFTER_CLOSE' => array(
		'message' => 'Whitespace after final close tag',
		'flag' => 'W17',
	),
	'WAR_DEPRICATED_TOKEN' => array(
		'message_extra' => '`%s` Depricated',
		'flag' => 'W18',
		'compare_array' => array(T_GLOBAL)
	),
	'WAR_WS_BEFORE_OPEN' => array(
		'message' => 'Whitespace before first open tag',
		'flag' => 'W19',
	),
	/*
	 *	Documentaion 
	 */
	'DOC_NO_DOCHEAD_FILE' => array(
		'message' => 'File not documented',
		'flag' => 'D1',
	),
	'DOC_NO_DOCHEAD_CLASS' => array(
		'message' => 'Class not documented',
		'flag' => 'D2',
	),
	'DOC_NO_DOCHEAD_METHOD' => array(
		'message' => 'Method not documented',
		'flag' => 'D3',
	),
	'DOC_NO_DOCHEAD_FUNCTION' => array(
		'message' => 'Function not documented',
		'flag' => 'D4',
	),
	'DOC_NO_DOCHEAD_INTERFACE' => array(
		'message' => 'Interface not documented',
		'flag' => 'D5',
	),
	'DOC_NO_DOCHEAD_PROPERTY' => array(
		'message_extra' => 'Property not documented `%s`',
		'flag' => 'D6',
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
	'REF_STATIC_MIX' => array(
		'message' => 'Static and nonstatic methods mixed in class',
		'flag' => 'R12',
	),
	'REF_USE_ARGUMENTS' => array(
		'message_extras' => 'To many arguments in use clause %d(%d)',
		'flag' => 'R13',
		'compare' => 3
	),	
	'REF_DEEP_NESTING' => array(
		'message_extras' => 'Nesting level to deep %d(%d)',
		'flag' => 'R14',
		'compare' => 6
	),
	'REF_FILE_FUNCTIONS' => array(
		'message_extras' => 'Too many functions in file %d(%d)',
		'flag' => 'R15',
		'compare' => 50
	),
	'REF_CLASS_METHODS' => array(
		'message_extras' => 'Too many methods in class %d(%d)',
		'flag' => 'R16',
		'compare' => 50
	),
	'REF_CLASS_PROPERTYS' => array(
		'message_extras' => 'Too many propertys in class %d(%d)',
		'flag' => 'R17',
		'compare' => 50
	),
	'REF_FILE_GLOBALS' => array(
		'message_extras' => 'Too many globals in file %d(%d)',
		'flag' => 'R18',
		'compare' => 50
	),
	'REF_HTML_BEFORE_OPEN' => array(
		'message' => 'HTML output before first open tag',
		'flag' => 'R19',
	),
	'REF_FILE_LENGTH' => array(
		'message_extras' => 'File to long %d(%d)',
		'flag' => 'R20',
		'compare' => 4000
	),
	'REF_DEPRECATED_NAME' => array(
		'message_extras' => '`%s` has been deprecated, Use `%s`',
		'flag' => 'R21',
		'compare' => array()
	),
	/*
	 * Security 
	 */
	'SEC_ERROR_REQUEST' => array(
		'message_extra' => 'GET/POST/REQUEST/FILE used directly in unsecure function: `%s`',
		'flag' => 'S1',
		'penalty' => 10.0,
	),
	'SEC_ERROR_INCLUDE' => array(
		'message_extra' => 'Unsecure use of: `%s`',
		'flag' => 'S2',
		'penalty' => 10.0,
	),
	'SEC_ERROR_CALLBACK' => array(
		'message_extra' => 'Unsecure callback in: `%s`',
		'flag' => 'S3',
		'penalty' => 10.0,
	),
);