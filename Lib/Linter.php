<?php
/**
----------------------------------------------------------------------+
* @desc			PHPLinter
----------------------------------------------------------------------+
* @file 		Linter.php
* @author 		Jóhann T. Maríusson <jtm@robot.is>
* @copyright     
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
require_once dirname(__FILE__) . '/constants.php';
/**
----------------------------------------------------------------------+
* @desc 	Wrapper class for element
----------------------------------------------------------------------+
*/
class Element {
	/**
	----------------------------------------------------------------------+
	* @desc 	Create blank element
	----------------------------------------------------------------------+
	*/
	public function __construct() {
		$this->visibility = false;
		$this->abstract = false;
		$this->static = false;
	}
}
/**
----------------------------------------------------------------------+
* @desc 	Linter. Measures code and splits into elements.
----------------------------------------------------------------------+
*/
class PHPLinter {
	/* @var Array */
	protected $options;
	/* @var Array */
	protected $rules;
	/* @var float */
	protected $score;
	/* @var Array */
	protected $tokens;
	/**
	----------------------------------------------------------------------+
	* @desc 	Create new linter instance
	* @param	String	Filename
	* @param	int		Flags
	* @param	String	Use rules
	* @param	String	Filename
	* @param	Array	Override Ruleset
	----------------------------------------------------------------------+
	*/
	public function __construct($file, $opt=0, $rules=null, $override=null, $rules_file=null) {
		$this->options 	= $opt;
		$this->file 	= $file;
		exec('php -l ' . escapeshellarg($file), $error, $code);
		if($code === 0) { 
			$this->tokens 	= Tokenizer::tokenize($file);
			$this->tcount 	= count($this->tokens);
			$this->score 	= 0;
			$this->ignore_next = array();
			$this->scope	= array();
			
			if(!empty($rules_file)) { 
				if(file_exists($rules_file)) {
					$this->rules = require $rules_file;
					$this->debug("Using user supplied rulesfile `$rules_file`\n", 0, OPT_VERBOSE);
				}
			} else {
				$this->rules = require dirname(__FILE__) . '/../rules/rules.php';
			}
			$this->globals = require dirname(__FILE__) . '/globals.php';
			
			if(is_array($override) && !empty($override)) {
            foreach($override as $k=>$_)
            	$this->rules[$k] = array_merge($this->rules[$k], $_);
			}
			
			// View only certain rules
			if(!empty($rules))  {
				$rules = preg_split('/\|/u', $rules);
				foreach($this->rules as &$_) {
					$_['used'] = in_array($_['flag'], $rules);
 				}
 				unset($_);
			}
		} else {
			$this->score = false;
		}
		$this->report 	= array();
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Internal profiling
	* @param	Bool
	----------------------------------------------------------------------+
	*/
	protected function profile($flushmsg=false) {
		if(defined('PHPL_PROFILE_ON')) {
			$now = microtime(true);
			if($flushmsg) {
				$time = $this->ptime - $now;
				echo "$time -> $flushmsg\n";
			} else {
				$this->ptime = $now;
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Lint current file
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public function lint() {
		if(is_null($this->tokens)) {
			$this->debug("Syntax error in file.. Skipping\n", 0, OPT_VERBOSE);
		} elseif($this->tcount === 0) {
			$this->debug("Empty file.. Skipping\n", 0, OPT_VERBOSE);
		} else {
			$element = $this->measure_file();
			$lint = new Lint_file($element, $this->rules, $this->options);
			$this->report = $lint->lint();
			$this->score = $lint->penalty();
			if(!empty($this->report)) {
				foreach($this->report as $_) $arr[] = $_['line'];
				array_multisort($arr, SORT_ASC, $this->report);
			}
		}
		return $this->report;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Measure file scope
	----------------------------------------------------------------------+
	*/
	protected function measure_file() {
		$this->profile();		
		$element 			= new Element();
		$element->type 		= T_FILE;
		$element->file	 	= $this->file;
		$element->parent	= $this->file;
		$element->name 		= $this->file;
		$element->owner 	= $this->file;
		$element->tokens 	= array();
		$element->depth 	= 0;
		$element 			= $this->measure(0, $element, $i);
		$element->end_line 	= $this->tokens[$i][2];
		$element->length 	= ($element->end_line - $element->start_line);
		$element->token_count = count($element->tokens);
		$this->profile('measure_file::' . $this->file);
		return $element;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Split token stream into elements of type function, comment,
	* 			class or method.
	* @param	$pos		int
	* @param	$in_name	String
	* @param	$in_type	int
	* @param	$depth		int
	* @return	int
	----------------------------------------------------------------------+
	*/
	protected function measure($pos, $element, &$ret) { 
		$start = $this->last_newline($pos);
		$element->start = $start;
		
		if($element->type === T_FILE) {
			$element->start_line = 1;
			$element->empty = false;
		} else {
			$element->start_line = $this->tokens[$start][2] + 1;
			$element->empty = true;
		}

		$this->debug(sprintf('In element `%s` of type `%s` line %d; Owned by `%s`'
							 ,$element->name
							 ,Tokenizer::token_name($element->type)
							 ,$element->start_line
							 ,$element->owner
					), $element->depth, OPT_DEBUG|OPT_SCOPE_MAP, true);
				
		$this->scope = array();
		
		$next_element = new Element();
		$tokens = $this->tokens;
		for($i = $pos; $i < $this->tcount; $i++) {
			if(count($this->scope) > 0 
				&& $element->empty
			    && Tokenizer::meaningfull($tokens[$i][0]) 
			    && $tokens[$i][0] !== T_CURLY_CLOSE) 
			{
				$element->empty = false;
			}
			if(!empty($this->ignore_next) 
				&& $tokens[$i][0] === $this->ignore_next[count($this->ignore_next) - 1]) 
			{
				$element->tokens[] = $tokens[$i];
				array_pop($this->ignore_next);
				continue;
			}
			switch($tokens[$i][0]) {
				case T_INLINE_HTML:
					// Gather inline html into one token
					$element->tokens[] = $tokens[$i];
					while(++$i < $this->tcount 
						&& in_array($tokens[$i][0], array(T_INLINE_HTML, T_NEWLINE)));
					$i--;
					break;
				case T_ELSE:
					// treat `else if` like `elseif`
					// perhaps the tokenizer should do this ?
					if($tokens[$this->next($i)][0] === T_IF) {
						$this->tokens[$i][0] = T_ELSEIF;
						$this->tokens[$i][1] = 'elseif';
						$this->ignore_next[] = T_IF;
					}
					$this->open_scope($i, $element);
					break;
				case T_IF:
				case T_ELSEIF:
				case T_THEN:
				case T_FOREACH:
				case T_WHILE:
				case T_SWITCH:
					$this->open_scope($i, $element);
					break;
				case T_FOR:
					$this->open_scope($i, $element);
					$this->ignore_next[] = T_SEMICOLON;
					$this->ignore_next[] = T_SEMICOLON;
					break;
				case T_BASIC_CURLY_OPEN:
					$this->open_scope($i, $element);
					break;
				case T_SEMICOLON:
					$this->close_scope($i, $element);
					$element->tokens[] = $tokens[$i];
					if(empty($this->scope) && $element->empty) {
						$i++;
						break 2;
					} 
					break;
				case T_CURLY_CLOSE:
					$this->close_scope($i, $element);
					if(empty($this->scope)) {
						$i++;
						break 2;
					}
					break;
				case T_CURLY_OPEN:
					$this->ignore_next[] = T_CURLY_CLOSE;
					break;
				case T_PUBLIC:
				case T_PRIVATE:
				case T_PROTECTED:
					$next_element->visibility = true;
					break;
				case T_ABSTRACT:
					$next_element->abstract = true;
					break;
				case T_STATIC:
					$next_element->static = true;
					break;
				case T_COMMENT:
					$element->tokens[] = $tokens[$i];
					$element->comments[] = $this->measure_comment($i, $element->depth, $i);
					break;
				case T_DOC_COMMENT:
					$element->tokens[] = $tokens[$i];
					$next_element->comments[] = $this->measure_comment($i, $element->depth, $i);
					break;
				case T_CLASS:
				case T_INTERFACE:
				case T_FUNCTION:
					list($type, $name, $owner) = $this->determine_type($i, $element, $next_element);
					$next_element->type = $type;
					$next_element->name = $name;
					$next_element->depth = $element->depth + 1;
					$next_element->owner = $owner;
					$element->tokens[] = $tokens[$i];
					// preserve scope
					$scope = $this->scope;
					$element->elements[] = $this->measure($i+1, $next_element, $i);
					$this->scope = $scope;
					$next_element = new Element();
					break;
				default:
					$element->tokens[] = $tokens[$i];
					break;
			}
		}
		// In case $i is over the buffer
		$element->end = ($i >= $this->tcount)
			? --$i : $i;
			
		$element->end_line = $tokens[$i][2];
		$element->length = ($element->end_line - $element->start_line);
		$element->token_count = count($element->tokens);
		$ret = ($i > 0) ? --$i : $i;
		$this->debug(sprintf('Exiting element `%s` of type `%s` line %d'
							 ,$element->name
							 ,Tokenizer::token_name($element->type)
							 ,$element->end_line
					 ), $element->depth, OPT_DEBUG|OPT_SCOPE_MAP, true);
		return $element;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Determine type of new element
	----------------------------------------------------------------------+
	*/
	protected function determine_type($pos, $element, &$next_element) {
		$tokens = $this->tokens;
		$next = $this->find($pos, array(T_STRING, T_PARENTHESIS_OPEN));
		$type = $tokens[$pos][0];
		if($next === false || $tokens[$next][0] === T_PARENTHESIS_OPEN) {
			// anonymous functions
			$name = 'anonymous';
			$type = T_ANON_FUNCTION;
		} else {
			$name = $tokens[$next][1];
			if(in_array($element->type, array(T_CLASS, T_INTERFACE)) 
				&& $tokens[$pos][0] == T_FUNCTION) 
			{
				$type = T_METHOD;
				if($element->type === T_INTERFACE) {
					$next_element->abstract = true;
				}
			}
		}
		if($type === T_METHOD || $type === T_ANON_FUNCTION) {
			$owner = $element->name;
		} else $owner = $element->owner;
		return array($type, $name, $owner);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Open scope, set scope token
	----------------------------------------------------------------------+
	*/
	protected function open_scope($pos, $element) {
		$token = $this->tokens[$pos];
		$scope = true;
		if($token[0] === T_BASIC_CURLY_OPEN && !empty($this->scope)) {
			// Scopes are closed by `;` if not opened by `{`.
			$last = array_pop($this->scope);
			if($last[0] !== T_BASIC_CURLY_OPEN) {
				$last[0] = T_BASIC_CURLY_OPEN;
				$scope = false;
			}
			$this->scope[] = $last;
		} 
		if($scope) {
			$depth = count($this->scope) + $element->depth;
			$this->debug(sprintf('Scope opened by `%s` line %d'
								 ,$token[1]
								 ,$token[2])
						,$depth, OPT_DEBUG|OPT_SCOPE_MAP, true);
			$this->scope[] = $token;
			$element->tokens[] = array(T_OPEN_SCOPE, $token[1], $token[2]);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Close scope, set scope token
	----------------------------------------------------------------------+
	*/
	protected function close_scope($pos, $element) {
		$token = $this->tokens[$pos];
		if($token[0] === T_SEMICOLON) {
			if(!empty($this->scope)) {
				// Nested scopes if not opened by `{` are 
				// terminated all at once.
				while($last = array_pop($this->scope)) {
					if($last[0] === T_BASIC_CURLY_OPEN) {
						$this->scope[] = $last;
						break;
					}
					$depth = count($this->scope) + $element->depth;
					$this->debug(sprintf('Scope closed by `%s` line %d'
										 ,$token[1]
										 ,$token[2])
								,$depth, OPT_DEBUG|OPT_SCOPE_MAP, true);
					$element->tokens[] = array(T_CLOSE_SCOPE, $token[1], $token[2]);
				}
			}
		} else {
			$depth = count($this->scope) + $element->depth;
			$this->debug(sprintf('Scope closed by `%s` line %d'
								 ,$token[1]
								 ,$token[2])
						,$depth, OPT_DEBUG|OPT_SCOPE_MAP, true);
			array_pop($this->scope);
			$element->tokens[] = array(T_CLOSE_SCOPE, $token[1], $token[2]);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Output debug info
	* @param	$out	String
	* @param	$depth	int
	----------------------------------------------------------------------+
	*/
	protected function debug($out, $depth=0, $mode=OPT_DEBUG, $smap=false) {
		if($this->options & $mode) {
			if($smap) {
				$tabs = str_pad('', $depth*2, "|\t");
			} else {
				$tabs = str_pad('', $depth, "\t");
			}
			echo "{$tabs}$out\n";
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Measure comment
	* @param	$pos	int
	* @param	$depth	int
	* @return	int
	----------------------------------------------------------------------+
	*/
	protected function measure_comment($pos, $depth, &$ret) {
		$element = new Element();
		$element->start = $pos;
		$element->start_line = $this->tokens[$pos][2];
		$element->type = $this->tokens[$pos][0];
		$element->name = 'comment';
		$depth += count($this->scope);
		
		$this->debug("In comment at {$element->start_line}", $depth);
		for($i = $pos;$i < $this->tcount;$i++) {
			if(Tokenizer::meaningfull($this->tokens[$i][0])) {
				$i--;
				break;
			}
			$element->tokens[] = $this->tokens[$i];
		}
		if($i === $this->tcount) $i--;
		$element->end = $i;
		$element->end_line = $this->tokens[$i][2];
		$this->debug("Exiting comment at {$element->end_line}", $depth);
		$ret = $i;
		return $element;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Find the next token.
	* @param	int 	Start
	* @param	mixed 	tokens to search for
	* @param	int	 	search limit
	* @return 	Int
	----------------------------------------------------------------------+
	*/
	protected function find($pos, $token, $limit=10) {
		$i = $pos;
		if(!is_array($token)) $token = array($token);
		while(true) {
			if(!isset($this->tokens[$i+1])) {
				return false;
			}
			if(in_array($this->tokens[++$i][0], $token)) {
				return $i;
			}
			if(!empty($limit) && ($i - $pos) == $limit)
				return false;
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Return the next meaningfull token
	* @param	int
	* @return	Int
	----------------------------------------------------------------------+
	*/
	protected function next($pos) {
		$i = $pos;
		while(true) {
			if(!isset($this->tokens[$i+1]))
				return false;
			if(Tokenizer::meaningfull($this->tokens[++$i][0]))
				return $i;
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Gather all token until not in $tokens
	* @param	int
	* @param	Array
	* @return	Int
	----------------------------------------------------------------------+
	*/
	protected function gather(&$pos, $tokens) {
		while(++$pos < $this->tcount) {
			if(!in_array($this->tokens[$pos][0], $tokens)) {
				break;
			}
			$tokens[] = $this->tokens[$pos];
		}
		return --$pos;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Return location of previous meaningfull token
	* @param	int
	* @return	Int
	----------------------------------------------------------------------+
	*/
	protected function prev($pos) {
		$i = $pos;
		while($i >= 0) {
			if(Tokenizer::meaningfull($this->tokens[--$i][0]))
				return $i;
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Report penalty
	* @return 	float
	----------------------------------------------------------------------+
	*/
	public function penalty() {
		return $this->score;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Report penalty
	* @return 	float
	----------------------------------------------------------------------+
	*/
	public function score() {
		if($this->score === false) return false;
		return round(floatval(SCORE_FULL + $this->score), 2);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Find the last newline token.
	* @param	$pos	Int
	* @return	Int
	----------------------------------------------------------------------+
	*/
	protected function last_newline($pos) {
		$i = $pos;
		while($i > 0) {
			if($this->tokens[--$i][0] == T_NEWLINE)
				break;
		}
		return $i;
	}	
}