<?php
/**
----------------------------------------------------------------------+
* @desc			PHPLinter
----------------------------------------------------------------------+
* @file 		Linter.php
* @author 		Jóhann T. Maríusson <jtm@hi.is>
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
class Element {}
/**
----------------------------------------------------------------------+
* @desc 	Linter. Measures code and splits into elements.
----------------------------------------------------------------------+
*/
class PHPLinter {
	protected $options;
	protected $conf;
	protected $element;
	protected $score;
	protected $names;
	protected $called;
	/**
	----------------------------------------------------------------------+
	* @desc 	Create new linter instance
	* @param	$file	String
	* @param	$opt	int
	* @param	$conf	Array
	----------------------------------------------------------------------+
	*/
	public function __construct($file, $opt=0, $conf=null) {
		$odin = new Tokenizer($file);
		$this->file 	= $file;
		$this->tokens 	= $odin->tokenize();
		$this->tcount 	= count($this->tokens);
		$this->report 	= array();
		$this->options 	= $opt;
		$this->score 	= 0;
		
		$this->conf = require dirname(__FILE__) . '/rules.php';
		$this->globals = require dirname(__FILE__) . '/globals.php';
		if(is_array($conf)) {
			foreach($conf as $k=>$_)
			$this->conf[$k] = array_merge($this->conf[$k], $_);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Lint current file
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public function lint() {
		$this->measure_file();
		$this->debug("\nSTART LINT...", 0, OPT_DEBUG_EXTRA);
		$lint = new Lint_file($this->element, $this->conf, $this->options);
		$this->report = $lint->lint();
		$this->score = $lint->penalty();
		$this->debug("END LINT...\n", 0, OPT_DEBUG_EXTRA);
		$arr = Set::column($this->report, 'line');
		array_multisort($arr, SORT_ASC, $this->report);
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
		$this->debug("START MEASURE ...");
		$this->debug("In $this->file of type: T_FILE");	
		
		$element = new Element();
		$element->type = T_FILE;
		$element->file = $this->file;
		$element->parent = $this->file;
		$element->name = $this->file;
		$element->tokens = array();
		$element->start_line = 1;
		
		$comments = array();
		for($i = 0;$i < $this->tcount;$i++) {
			switch($this->tokens[$i][0]) {
				case T_DOC_COMMENT:
					$element->tokens[] = $this->tokens[$i];
					$comments[] = $this->measure_comment($i, 0, $i);
					break;
				case T_COMMENT:
					$element->tokens[] = $this->tokens[$i];
					$element->comments[] = $this->measure_comment($i, $element->depth, $i);
					break;
				case T_CLASS:
				case T_FUNCTION:
				case T_INTERFACE:
					$inelem = new Element();
					$inelem->type = $this->tokens[$i][0];
					$inelem->name = $this->tokens[$this->find($i, T_STRING)][1];
					$inelem->depth = 0;
					$inelem->owner = $this->file;
					$inelem->abstract = false;
					$inelem->comments = $comments;
					$element->tokens[] = array($inelem->type);
					$element->elements[] = $this->measure($i+1, $inelem, $i);
					$comments = array();
					$element->tokens[] = array($this->tokens[$i]);
					break;
				default:
					if(!isset($element->start)) {
						$element->start = $i;
					}
					$element->tokens[] = $this->tokens[$i];
					break;
			}
		}
		if(empty($element->tokens)) {
			return false;
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
				),++$element->depth);
				
		$body = false;
		$abstract = false;
		$visibility = false;
		$comments = array();
		$this->names[] = $element->name;
		// Save tokens from last newline
		foreach(range($start, $pos-1) as $_)
			$element->tokens[] = $this->tokens[$_];
		// measure
		for($i = $pos,$clvl = 0;$i < $this->tcount;$i++) {
			if($clvl > 0 && $element->empty && 
				$this->meaningfull($this->tokens[$i][0])) {
				$element->empty = false;
			}
			switch($this->tokens[$i][0]) {
				case T_CURLY_OPEN:
					$this->debug("Scope opened", $element->depth);
					$clvl++;
					$body = true;
					break;
				case T_CURLY_CLOSE:
					$this->debug("Scope closed", $element->depth);
					if(--$clvl == 0) {
						$i++;
						break 2;
					}
					break;
				case T_DOC_COMMENT:
					$element->tokens[] = $this->tokens[$i];
					$comments[] = $this->measure_comment($i, $element->depth, $i);
					break;
				case T_COMMENT:
					$element->tokens[] = $this->tokens[$i];
					$element->comments[] = $this->measure_comment($i, $element->depth, $i);
					break;
				case T_ABSTRACT:
					$abstract = true;
					break;
				case T_SEMICOLON:
					if($element->abstract === true 
						&& $element->type == T_METHOD) {
						break 2;
					}
					break;
				case T_PUBLIC:
				case T_PRIVATE:
				case T_PROTECTED:
					$element->tokens[] = $this->tokens[$i];
					$visibility = $this->tokens[$i][0];
					break;
				case T_CLASS:
				case T_INTERFACE:
				case T_FUNCTION:
					$next = $this->tokens[$this->find($i, T_STRING)][1];
					$type = (in_array($element->type, array(T_CLASS, T_INTERFACE))
						&& $this->tokens[$i][0] == T_FUNCTION)
						? T_METHOD 
						: $this->tokens[$i][0];
					if($type == T_METHOD) {
						$owner = $element->name;
					} else $owner = $element->owner;
					// Recurs
					$inelem = new Element();
					$inelem->type = $type;
					$inelem->name = $next;
					$inelem->depth = $element->depth;
					$inelem->owner = $owner;
					$inelem->visibility = $visibility;
					$inelem->abstract = ($element->type == T_INTERFACE) 
							? true : $abstract;
					$inelem->comments = $comments;
					$element->tokens[] = array($type, '*');
					$element->elements[] = $this->measure($i+1, $inelem, $i);
					$comments = array();
					$visibility = false;
					break;
				default:
					$element->tokens[] = $this->tokens[$i];
					break;
			}
		}
		// In case $i is over the buffer
		$element->end = ($i >= $this->tcount)
			? --$i : $i;
			
		// Abstracts and interfaces
		if($element->empty && !$body) {
			$element->empty = false;
		}
		$element->end_line = $this->tokens[$i][2];
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
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
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
	* @desc 	Is the token meaningfull, used to determine if an element
	* 			is empty.
	* @param	$token	int
	* @return 	Bool
	----------------------------------------------------------------------+
	*/
	protected function meaningfull($token) {
		return (!in_array($token, array(
			T_WHITESPACE, T_NEWLINE, T_COMMENT, T_DOC_COMMENT,
			T_CURLY_CLOSE // Closing bracer of element
		)));
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
		$element->type = T_DOC_COMMENT;
		$element->name = 'comment';
		
		$this->debug("In comment at {$element->start_line}", $depth);
		for($i = $pos;$i < $this->tcount;$i++) {
			if(!in_array($this->tokens[$i][0], array(
				T_COMMENT, T_DOC_COMMENT, T_NEWLINE
			))) {
				$i--;
				break;
			}
			$element->tokens[] = $this->tokens[$i];
		}
		
		$element->end = $i;
		$element->end_line = $this->tokens[$i][2];
		$this->debug("Exiting comment at {$element->end_line}", $depth);
		$ret = $i;
		return $element;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Find the next T_STRING token.
	* @param	$pos	Int 	Start
	* @return 	Int
	----------------------------------------------------------------------+
	*/
	protected function find($pos, $token, $limit=10) {
		$i = $pos;
		while(true) {
			if(!isset($this->tokens[$i+1]))
				return false;
			if($this->tokens[++$i][0] == $token)
				return $i;
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
			if($this->meaningfull($this->tokens[++$i][0]))
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
			if($this->meaningfull($this->tokens[--$i][0]))
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
		return 10.0 + $this->score;
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