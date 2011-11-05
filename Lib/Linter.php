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
	/* @var Object */
	protected $element;
	/* @var float */
	protected $score;
	/* @var Array */
	protected $names;
	/* @var Array */
	protected $called;
	/* @var Array */
	protected $tokens;
	/**
	----------------------------------------------------------------------+
	* @desc 	Create new linter instance
	* @param	String	Filename
	* @param	int		Flags
	* @param	Array	Ruleset
	----------------------------------------------------------------------+
	*/
	public function __construct($file, $opt=0, $rules=null) {
		$this->options 	= $opt;
		$this->file 	= $file;
		exec('php -l ' . escapeshellarg($file), $error, $code);
		if($code === 0) { 
			$this->tokens 	= Tokenizer::tokenize($file);
			$this->tcount 	= count($this->tokens);
			$this->report 	= array();
			$this->score 	= 0;
			
			$this->rules = require dirname(__FILE__) . '/../rules/rules.php';
			$this->globals = require dirname(__FILE__) . '/globals.php';
			if(is_array($rules) && !empty($rules)) {
				foreach($rules as $k=>$_)
					$this->rules[$k] = array_merge($this->rules[$k], $_);
			}
		}
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
			return array();
		}
		if($this->tcount === 0) {
			$this->debug("Empty file.. Skipping\n", 0, OPT_VERBOSE);
			return array();
		} 
		$this->measure_file();
		$this->debug("\nSTART LINT...", 0, OPT_DEBUG_EXTRA);
		$lint = new Lint_file($this->element, $this->rules, $this->options);
		$this->report = $lint->lint();
		$this->score = $lint->penalty();
		$this->debug("END LINT...\n", 0, OPT_DEBUG_EXTRA);
		
		if(!empty($this->report)) {
			foreach($this->report as $_) $arr[] = $_['line'];
			array_multisort($arr, SORT_ASC, $this->report);
		}
		
		return $this->report;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Harvest a list of names.
	* @param	$names	Array
	* @return	Array
	----------------------------------------------------------------------+
	*/
	public function harvest($names=null) {
		foreach(array(
				0 => $this->names,
				1 => $this->called
			) as $k=>$v) {
			foreach($v as $_) {
				if(is_array($names) && in_array($_, array_keys($names))) {
					$names[$_]++;
				} else {
					$names[$_] = $k;
				}
			}
		}
		return $names;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Measure file scope
	----------------------------------------------------------------------+
	*/
	protected function measure_file() {
		$this->profile();
		$this->debug("START MEASURE ...");
		$this->debug("In $this->file of type: T_FILE");	
		
		$element = new Element();
		$element->type = T_FILE;
		$element->file = $this->file;
		$element->parent = $this->file;
		$element->name = $this->file;
		$element->tokens = array();
		$element->start_line = 1;
		$element->depth = 0;
		$next_element = new Element();
		
		for($i = 0;$i < $this->tcount;$i++) {
			switch($this->tokens[$i][0]) {
				case T_COMMENT:
				case T_DOC_COMMENT:
					$comment = $this->measure_comment($i, 0, $i);
					if($comment->type === T_DOC_COMMENT) {
						$next_element->comments[] = $comment;
					} else {
						$element->comments[] = $comment;
					}
					break;
				case T_CLASS:
				case T_FUNCTION:
				case T_INTERFACE:
					$next_element->type = $this->tokens[$i][0];
					$next_element->name = $this->tokens[$this->find($i, T_STRING)][1];
					$next_element->depth = 1;
					$next_element->owner = $this->file;
					$element->tokens[] = $this->tokens[$i];
					$element->elements[] = $this->measure($i+1, $next_element, $i);
					$next_element = new Element();
					break;
				default:
					if(!isset($element->start)) {
						$element->start = $i;
					}
					$element->tokens[] = $this->tokens[$i];
					break;
			}
		}
		
		// In case $i is over the buffer
		$element->end = ($i >= $this->tcount)
			? --$i : $i;
		$element->end_line = $this->tokens[$i][2];
		$element->length = ($element->end_line - $element->start_line);
		$element->token_count = count($element->tokens);
		$this->element = $element;
		
		$this->debug("Exiting $this->file of type: T_FILE");
		$this->debug("END MEASURE ...");
		$this->profile('measure_file::'.$this->file);
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
		$element->start_line = $this->tokens[$start][2] + 1;
		$element->start = $start;
		$element->empty = true;

		$this->debug(sprintf('In element `%s` of type %s at %d; Owned by `%s`'
				,$element->name
				,Tokenizer::token_name($element->type)
				,$element->start_line
				,$element->owner
				),$element->depth);
				
		$next_element = new Element();
		$this->names[] = $element->name;
		$tokens = $this->tokens;
		for($i = $pos, $nesting = 0; $i < $this->tcount; $i++) {
			if($nesting > 0 
				&& $element->empty
			    && Tokenizer::meaningfull($this->tokens[$i][0]) 
			    && $this->tokens[$i][0] !== T_CURLY_CLOSE) 
			{
				$element->empty = false;
			}
			switch($tokens[$i][0]) {
				case T_SEMICOLON:
					$element->tokens[] = $tokens[$i];
					if($nesting === 0 && $element->empty) {
						$i++;
						break 2;
					} 
					break;
				case T_CURLY_CLOSE:
					$this->debug("Scope closed", $element->depth);
					$element->tokens[] = $tokens[$i];
					if(--$nesting === 0) {
						$i++;
						break 2;
					}
					break;
				case T_CURLY_OPEN:
					$this->debug("Scope opened", $element->depth);
					$nesting++;
					$element->tokens[] = $tokens[$i];
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
					$next = $this->find($i, array(T_STRING, T_PARENTHESIS_OPEN));
					$type = $tokens[$i][0];
					if($next === false || $tokens[$next][0] === T_PARENTHESIS_OPEN) {
						// anonymous functions
						$name = 'anonymous';
						$type = T_ANON_FUNCTION;
					} else {
						$name = $this->tokens[$next][1];
						if(in_array($element->type, array(T_CLASS, T_INTERFACE)) 
							&& $this->tokens[$i][0] == T_FUNCTION) 
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

					// Recurs
					$next_element->type = $type;
					$next_element->name = $name;
					$next_element->depth = $element->depth + 1;
					$next_element->owner = $owner;
					$element->tokens[] = $tokens[$i];
					$element->elements[] = $this->measure($i+1, $next_element, $i);
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
		$ret = --$i;
		$this->debug(sprintf('Exiting element `%s` of type %s at %d'
				,$element->name
				,Tokenizer::token_name($element->type)
				,$element->end_line
				), $element->depth);
		return $element;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Output debug info
	* @param	$out	String
	* @param	$depth	int
	----------------------------------------------------------------------+
	*/
	protected function debug($out, $depth=0, $mode=OPT_DEBUG) {
		if($this->options & $mode) {
			$tabs = str_pad('', $depth, "\t");
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
	* @param	$pos	Int 	Start
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
	* @param	$pos	int
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
	* @desc 	Return location of previous meaningfull token
	* @param	$pos	int
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
		return round(floatval(10.0 + $this->score), 2);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Find the last newline, i.e. the beginning of the element.
	* @param	$pos	Int
	* @return	Int
	----------------------------------------------------------------------+
	*/
	protected function last_newline($pos) {
		$i = $pos;
		while(true) {
			if($this->tokens[--$i][0] == T_NEWLINE)
				return $i;
			if($i == 0)
				return $i;
		}
	}	
}