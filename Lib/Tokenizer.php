<?php
/**
----------------------------------------------------------------------+
*  @desc			Tokenizer. Tokenize file and turn all tokens into 
*  					token arrays.
----------------------------------------------------------------------+
*  @file 			Tokenizer.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    Oct 29, 2011
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
namespace PHPLinter;
class Tokenizer {
	/**
	----------------------------------------------------------------------+
	* @desc 	Tokenize file.
	* @return	token array
	----------------------------------------------------------------------+
	*/
	public static function tokenize($file, $skip_whitespace=true) {
		$eol = "\n";
		$all = token_get_all(file_get_contents($file));
		$lnum = 1;
		$out = array();
		foreach($all as $token) {
			// Make all tokens arrays
			if(!is_array($token)) {
				$token = array(
					self::tokenChar($token),
					$token,
					$lnum
				);
			} 

            // Find newlines and mark
			if(mb_strpos($token[1], $eol) !== false) {
				$string = str_split($token[1]);
				$substr = '';
				foreach($string as $_) {
					if($_ == $eol) {
						if(!empty($substr)) {
							$tkn = array(
								$token[0],
								$substr,
								$token[2]
							);
							$out[] = $tkn;
						}
						$tkn = array(
							T_NEWLINE,
							$eol,
							$lnum
						);
						$out[] = $tkn;
						$substr = '';
						$lnum++;
					} else $substr .= $_;
				}
				if(!empty($substr)) {
					$tkn = array(
						$token[0],
						$substr,
						$token[2]
					);
					$out[] = $tkn;
				}
				continue;
			}
			// Make distinction
			if($token[0] == T_STRING) {
				$token[0] = self::tokenString($token[1]);
			}
			
			// Save token
			$out[] = $token;
		}
		if($skip_whitespace) {
			foreach($out as $_) {
				if($_[0] !== T_WHITESPACE) $ret[] = $_;
			}
		}
		return empty($ret) ? $out : $ret;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Create new tokens from chars
	* @param	$char	String
	* @return	int
	----------------------------------------------------------------------+
	*/
	public static function tokenChar($char) {
		switch($char) {
			case '{': return T_CURLY_OPEN;
			case '}': return T_CURLY_CLOSE;
			case '[': return T_SQUARE_OPEN;
			case ']': return T_SQUARE_CLOSE;
			case '(': return T_PARENTHESIS_OPEN;
			case ')': return T_PARENTHESIS_CLOSE;
			case '.': return T_STR_CONCAT;
			case ';': return T_SEMICOLON;
			case ':': return T_COLON;
			case '?': return T_THEN;
			case '=': return T_EQUALS;
			case '!': return T_NOT;
			case '`': return T_BACKTICK;
			default:  return T_IGNORE;
		}
	} 
	/**
	----------------------------------------------------------------------+
	* @desc 	Create new tokens from T_STRING
	* @param	$string		String
	* @return	int
	----------------------------------------------------------------------+
	*/
	public static function tokenString($string) {
		switch(mb_strtolower($string)) {
	        case 'false': 	return T_FALSE;
	        case 'true': 	return T_TRUE;
	        case 'null':	return T_NULL;
	        case 'self':	return T_SELF;
	        case 'parent':	return T_PARENT;
	        default: return T_STRING;
		}
	} 
	/**
	----------------------------------------------------------------------+
	* @desc 	Names of the new tokens
	* @param	$token	int
	* @return	String
	----------------------------------------------------------------------+
	*/
	public static function token_name($token) {
		switch($token) {
			case T_IGNORE: 				return 'T_IGNORE';
			case T_NEWLINE: 			return 'T_NEWLINE';
			case T_CURLY_CLOSE: 		return 'T_CURLY_CLOSE';
			case T_SQUARE_OPEN:			return 'T_SQUARE_OPEN';
			case T_SQUARE_CLOSE:		return 'T_SQUARE_CLOSE';
			case T_PARENTHESIS_OPEN:	return 'T_PARENTHESIS_OPEN';
			case T_PARENTHESIS_CLOSE:	return 'T_PARENTHESIS_CLOSE';
			case T_STR_CONCAT:			return 'T_STR_CONCAT';
			case T_SEMICOLON:			return 'T_SEMICOLON';
			case T_COLON:				return 'T_COLON';
			case T_EQUALS:				return 'T_EQUALS';
			case T_THEN:				return 'T_THEN';
			case T_METHOD:				return 'T_METHOD';
			case T_ANON_FUNCTION:		return 'T_ANON_FUNCTION';
			case T_TRUE:				return 'T_TRUE';
			case T_FALSE:				return 'T_FALSE';
			case T_NULL:				return 'T_NULL';
			case T_SELF:				return 'T_SELF';
			case T_PARENT:				return 'T_PARENT';
			default:
				return token_name($token);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Is token meaningfull.
	* @param	int
	* @return 	Bool
	----------------------------------------------------------------------+
	*/
	public static function meaningfull($token) {
		switch($token) {
			case T_WHITESPACE:
			case T_NEWLINE:
			case T_COMMENT: 
			case T_DOC_COMMENT:
			case T_IGNORE:
				return false;
			default:
				return true;
		}
	}
} 
?>