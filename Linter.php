<?php
/**
----------------------------------------------------------------------+
* @desc			OdinLinter
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
require 'default_config.php';
require 'Tokenizer.php';
require 'Set.php';
require_once 'constants.php';

class PHPLinter {
	protected $options;
	protected $conf;
	protected $data;
	protected $elements;
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
		
		$this->conf = require 'default_config.php';
		if(is_array($conf)) {
			foreach($conf as $k=>$_)
			$this->conf[$k] = array_merge($this->conf[$k], $_);
		}
		$this->data = array();
		$this->elements = array();
		$this->called = array();
		$this->names = array();
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Lint current file
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public function lint() {
		$lnum = 1;
		$comment = false;
		$vars 	= array();
		$fp = explode('/', $this->file);
		$pretty_file_name = $fp[count($fp)-1];
		foreach(file($this->file) as $_) {
			$len = strlen($_);
			if($len > $this->conf['CON_LINE_LENGTH']['compare']) {
				$elem = array(
					'PARENT' => $pretty_file_name,
					'START_LINE' => $lnum
				);
				$this->report($elem, 'CON_LINE_LENGTH', $len);
			}
			$lnum++;
		}

		$this->debug("START ...");
		$this->measure_file();
		$arr = Set::column($this->elements, 'START_LINE');
		array_multisort($arr, SORT_ASC, $this->elements);
		$this->debug("END ...");
		$this->debug("\nELEMENTS FOUND...", 0, OPT_DEBUG_EXTRA);
		$ecnt = count($this->elements);
		for($i=0; $i<$ecnt; $i++) {
			$this->debug("Element: " . Tokenizer::token_name($this->elements[$i]['TYPE']), 
						 0, OPT_DEBUG_EXTRA);
			$scope = $this->elements[$i]['TYPE'];
			if($scope == T_DOC_COMMENT)
				continue;
			if($i == 0 || !in_array($this->elements[$i-1]['TYPE'], 
									array(T_DOC_COMMENT))) {
				switch($scope) {
					case T_CLASS:
						$r = 'ERR_NO_DOCHEAD_CLASS';
						break;
					case T_FUNCTION:
						$r = 'ERR_NO_DOCHEAD_FUNCTION';
						break;
					case T_METHOD:
						$r = 'ERR_NO_DOCHEAD_METHOD';
						break;
					case T_INTERFACE:
						$r = 'ERR_NO_DOCHEAD_INTERFACE';
						break;
					case T_FILE:
						$this->elements[$i]['START_LINE'] = 1;
						$r = 'ERR_NO_DOCHEAD_FILE';
						break;
				}
				// special case for class files
				if(!($i > 0 && $scope == T_CLASS 
					&& $this->elements[$i-1]['TYPE'] == T_FILE))
					$this->report($this->elements[$i], $r);
			}
			$this->parse($this->elements[$i]);
		}
		$this->debug("END ELEMENTS FOUND...", 0, OPT_DEBUG_EXTRA);
		$arr = Set::column($this->report, 'line');
		array_multisort($arr, SORT_ASC, $this->report);
		
		return $this->report;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Tokens common to all scopes.
	* @param	$element	Array
	* @param	$pos		int
	----------------------------------------------------------------------+
	*/
	protected function common_tokens($element, $pos) {
		switch($this->tokens[$pos][0]) {
			case T_CLOSE_TAG:
				$this->report($element, 'REF_HTML_MIXIN');
				break;
			case T_EVAL:
				$this->report($element, 'INF_EVAL_USED');
				break;
			case T_STRING:
				$this->parse_string($pos, $element['NAME']);
				break;
			default:
				$t = $this->tokens[$pos][0];
				if(in_array($t, array_keys($this->conf['DPR_DEPRICATED_TOKEN']['compare']))) {
					$this->report($element, 'DPR_DEPRICATED_TOKEN',
						$this->conf['DPR_DEPRICATED_TOKEN']['compare'][$t]);		
				}
				break;		
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse element.
	* @param	$element	Array
	----------------------------------------------------------------------+
	*/
	protected function parse($element) {
		switch($element['TYPE']) {
			case T_CLASS:
				if($element['EMPTY']) {
					$this->report($element, 'WAR_EMPTY_CLASS');
				}
				$regex = $this->conf['CON_CLASS_NAME']['compare'];
				if(!preg_match($regex, $element['NAME']))
					$this->report($element, 'CON_CLASS_NAME', $regex);
				$this->parse_class($element);
				break;
			case T_INTERFACE:
				if($element['EMPTY']) {
					$this->report($element, 'WAR_EMPTY_INTERFACE');
				}
				$regex = $this->conf['CON_CLASS_NAME']['compare'];
				if(!preg_match($regex, $element['NAME']))
					$this->report($element, 'CON_INTERFACE_NAME', $regex);
				$this->parse_class($element);
				break;
			case T_FUNCTION:
			case T_METHOD:
				if($element['TYPE'] == T_FUNCTION) {
					$namecomp = 'CON_FUNCTION_NAME';
					$emptycomp = 'WAR_EMPTY_FUNCTION';
				} else {
					$namecomp = 'CON_METHOD_NAME';
					$emptycomp = 'WAR_EMPTY_METHOD';
				}
				if($element['EMPTY']) 
					$this->report($element, $emptycomp);
				$regex = $this->conf[$namecomp]['compare'];
				if(!(substr($element['NAME'], 0, 2) == '__') 
					&& !preg_match($regex, $element['NAME']))
					$this->report($element, $namecomp, $regex);
				$this->parse_function($element);
				break;
			case T_FILE:
				$this->parse_file($element);
				break;
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse class element
	* @param	$element	Array
	----------------------------------------------------------------------+
	*/
	protected function parse_class($element) {
		$tcnt = count($element['TOKENS']);
		$et = $element['TOKENS'];
		$locals 	= array();
		for($i=0;$i<$tcnt;$i++) {
			switch($this->tokens[$et[$i]][0]) {
				case T_PUBLIC:
				case T_PRIVATE:
				case T_PROTECTED:
					break;
				case T_VAR:
					$this->report($element, 'WAR_OLD_STYLE_VARIABLE');
					break;
				case T_VARIABLE:
					$locals[] = substr($this->tokens[$et[$i]][1], 1);
					break;
				default:
					$this->common_tokens($element, $et[$i]);
					break;
			}
		}
		$len = $element['END_LINE'] - $element['START_LINE'];
		
		if($len > $this->conf['REF_CLASS_LENGTH']['compare'])
			$this->report($element, 'REF_CLASS_LENGTH', $len);	
		
		$name = $element['NAME'];
		
		if(!empty($this->data[$name]['THIS'])) {
			if(is_array($this->data[$name]['METHODS'])) {
				$this->data[$name]['METHODS'] = array_diff($this->data[$name]['THIS'], 
														   $this->data[$name]['METHODS']);
			}
			$vars = array_diff($locals, $this->data[$name]['THIS']);
		}
		
		if(!empty($this->data[$name]['METHODS']) && 
			in_array($name, $this->data[$name]['METHODS']))
			$this->report($element, 'WAR_OLD_STYLE_CONSTRUCT');
			
		foreach($vars as $_) {
			$this->report($element, 'WAR_UNUSED_VAR', $_);	
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse function/method element
	* @param	$element	Array
	----------------------------------------------------------------------+
	*/
	protected function parse_function($element) {
		$tcnt 		= count($element['TOKENS']);
		$et 		= $element['TOKENS'];
		$args		= false;
		$locals 	= array();
		$branches 	= 0;
		$visibility = null;
		for($i = 0;$i < $tcnt;$i++) {
			switch($this->tokens[$et[$i]][0]) {
				case T_PUBLIC:
				case T_PRIVATE:
				case T_PROTECTED:
					$visibility = true;
					break;
				case T_PARENTHESIS_OPEN:
					if($args === false) {
						$args = $this->parse_args($i, $et);
					}
					break;
				case T_SWITCH:
				case T_IF:
				case T_ELSE:
				case T_ELSEIF:
					$branches++;
					break;
				case T_VARIABLE:
					$locals[] = $this->tokens[$et[$i]][1];
					break;
				default:
					$this->common_tokens($element, $et[$i]);
					break;
			}
		}
		if(empty($visibility) && $element['TYPE'] == T_METHOD)
			$this->report($element, 'CON_NO_VISIBILITY');
		$locals = array_unique($locals);
		$compares = array(
			'REF_ARGUMENTS' => count($args),
			'REF_LOCALS' => count($locals),
			'REF_BRANCHES' => $branches,
		);
		$len = ($element['END_LINE'] - $element['START_LINE']);
		if($element['TYPE'] == T_METHOD) {
			$compares['REF_METHOD_LENGTH'] = $len;
		} else {
			$compares['REF_FUNCTION_LENGTH'] = $len;
		}
		
		foreach($compares as $k => $_)
			if($_ > $this->conf[$k]['compare'])
				$this->report($element, $k, $_);

		if(!empty($args))
			foreach($args as $_)
				if(!in_array($_, $locals))
					$this->report($element, 'WAR_UNUSED_ARG', $_);	
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	FIXME
	* @return	FIXME
	----------------------------------------------------------------------+
	*/
	protected function parse_file($element) {
		$tcnt = count($element['TOKENS']);
		$et = $element['TOKENS'];
		for($i = 0;$i < $tcnt;$i++) {
			$element['START_LINE'] = $this->tokens[$et[$i]][2];			
			switch($this->tokens[$et[$i]][0]) {
				case T_CLOSE_TAG:
					if($this->find($et[$i], T_OPEN_TAG, null) === false) {
						if(count($this->tokens) - $et[$i] > 1)
							if($this->next($et[$i]))
								$this->report($element, 'REF_HTML_AFTER_CLOSE');
							else
								$this->report($element, 'WAR_WS_AFTER_CLOSE');
					} else {
						$this->common_tokens($element, $et[$i]);
					}
					break;
				default:
					$this->common_tokens($element, $et[$i]);
					break;
			}
		}
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
	* @desc 	Parse argument-list
	* @param	$i		int
	* @param	$et		Array
	* @return	Array
	----------------------------------------------------------------------+
	*/
	protected function parse_args(&$i, $et) {
		$out = array();
		while(true) {
			switch($this->tokens[$et[++$i]][0]) {
				case T_VARIABLE:
					$out[] = $this->tokens[$et[$i]][1];
					break;
				case T_PARENTHESIS_CLOSE:
					return $out;
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Measure file scope
	----------------------------------------------------------------------+
	*/
	protected function measure_file() {
		$this->debug("In $this->file of type: T_FILE");	
		$element = array(
			'TYPE' => T_FILE,
			'PARENT' => $this->file,
			'NAME' => $this->file,
			'TOKENS' => array(),
		);
		for($i = 0;$i < $this->tcount;$i++) {
			switch($this->tokens[$i][0]) {
				case T_DOC_COMMENT:
					$i = $this->measure_comment($i, 0);
					break;
				case T_CLASS:
				case T_FUNCTION:
				case T_INTERFACE:
					$next = $this->tokens[$this->find($i, T_STRING)][1];
					$i = $this->measure($i+1, $next, $this->tokens[$i][0], 0, $this->file);
					break;
				case T_STRING:
					$this->parse_string($i, $this->file);
					break;
				case T_OPEN_TAG:
				case T_INLINE_HTML:
					if(isset($this->tokens[$i+1]) && $this->tokens[$i+1][0] == T_NEWLINE)
						// skip
						$i++;
					continue 2;
				default:
					if(!isset($element['START'])) {
						$element['START'] = $i;
						$element['START_LINE'] = isset($this->tokens[$i+1])
							? $this->tokens[$i+1][2]
							: $this->tokens[$i][2];
					}
					$element['TOKENS'][] = $i;
					break;
			}
		}
		if(empty($element['TOKENS'])) {
			return false;
		}
		// In case $i is over the buffer
		$element['END'] = ($i >= $this->tcount)
			? --$i : $i;
		$element['END_LINE'] = $this->tokens[$i][2];
		$this->elements[] = $element;
		$this->debug("Exiting $this->file of type: T_FILE");
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse a string token
	* @param	$i	int
	----------------------------------------------------------------------+
	*/
	protected function parse_string($i, $parent) {
		//echo "Found: " . $this->tokens[$i][1];
		$nt = $this->next($i);
		if($nt == T_PARENTHESIS_OPEN || 
			$nt == T_DOUBLE_COLON) {
			$this->called[] = $this->tokens[$i][1];
			//echo " Saved\n";
		} //else echo " Ignored\n";
		if(in_array($this->tokens[$i][1], 
			$this->conf['DPR_DEPRICATED_STRING']['compare'])) {
			$e = array(
				'PARENT' => $parent,
				'START_LINE' => $this->tokens[$i][2]
			);
			$this->report($e, 'DPR_DEPRICATED_STRING', $this->tokens[$i][1]);		
		}
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
	protected function measure($pos, $in_name, $in_type, $depth, $owner=null) {
		$start = $this->last_newline($pos);
		$element = array(
			'START' => $start,
			'START_LINE' => $this->tokens[$start][2] + 1,
			'TYPE' => $in_type,
			'PARENT' => $owner,
			'NAME' => $in_name,
			'EMPTY' => true,
		);
		$this->debug("In element `$in_name` of type ".
				Tokenizer::token_name($in_type)
				. " at {$element['START_LINE']}; Owned by `$owner`", ++$depth);
		$body = false;
		$this->names[] = $in_name;
		// Save tokens from last newline
		foreach(range($start, $pos-1) as $_)
			$element['TOKENS'][] = $_;
		// measure
		for($i = $pos,$clvl = 0;$i < $this->tcount;$i++) {
			if($clvl > 0 && $element['EMPTY'] && 
				$this->meaningfull($this->tokens[$i][0])) {
				$element['EMPTY'] = false;
			}
			switch($this->tokens[$i][0]) {
				case T_CURLY_OPEN:
					$this->debug("Scope opened", $depth);
					$clvl++;
					$body = true;
					break;
				case T_CURLY_CLOSE:
					$this->debug("Scope closed", $depth);
					if(--$clvl == 0) {
						$i++;
						break 2;
					}
					break;
				case T_DOC_COMMENT:
					$i = $this->measure_comment($i, $depth);
					break;
				case T_CLASS:
				case T_FUNCTION:
				case T_INTERFACE:
					$next = $this->tokens[$this->find($i, T_STRING)][1];
					$type = (in_array($in_type, array(T_CLASS, T_INTERFACE))
						&& $this->tokens[$i][0] == T_FUNCTION)
						? T_METHOD 
						: $this->tokens[$i][0];
					if($type == T_METHOD) {
						$owner = $in_name;
						$this->add_data($owner, $next, T_METHOD);
					}
					// Recurse
					$i = $this->measure($i+1, $next, $type, $depth, $owner);
					break;
				case T_VARIABLE:
					/* $this never found anywhere but methods */
					if($this->tokens[$i][1] == '$this') {
						$j = $this->find($i, T_STRING, 3);
						if($j !== false) {
							$this->add_data($owner, $this->tokens[$j][1], T_VARIABLE);
							$i = $j;
						}
					} else {
						$element['TOKENS'][] = $i;
					}
					break;
				case T_EXTENDS:
					$next = $this->tokens[$this->find($i, T_STRING)][1];
					$this->called[] = $next;
				default:
					$element['TOKENS'][] = $i;
					break;
			}
		}
		// In case $i is over the buffer
		$element['END'] = ($i >= $this->tcount)
			? --$i : $i;
		// Abstracts and interfaces
		if($element['EMPTY'] && !$body) {
			 $element['EMPTY'] = false;
			 $element['END_LINE'] = $element['START_LINE'];
			 $ret = $this->find($element['START'], T_NEWLINE);
			 if($ret === false) {
			 	$ret = $this->find($element['START'], T_CURLY_CLOSE);
			 	if($ret === false)
			 		// giveup FIXME we need a meaningfull way to handle these cases
			 		$ret = --$i;
			 }
		} else {
			$element['END_LINE'] = $this->tokens[$i][2];
			$ret = --$i;
		}
		$this->elements[] = $element;
		$this->debug("Exiting element `$in_name` of type ".
				Tokenizer::token_name($in_type)
				. " at {$element['END_LINE']}", $depth);
		return $ret;
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
	protected function measure_comment($pos, $depth) {
		$element = array(
			'START' => $pos,
			'START_LINE' => $this->tokens[$pos][2],
			'TYPE' => T_DOC_COMMENT,
		);
		$this->debug("In comment at {$element['START_LINE']}", $depth);
		for($i = $pos;$i < $this->tcount;$i++) {
			if(preg_match('/(FIXME|TODO)/i', $this->tokens[$i][1], $m)) {
				$this->report($element, 'INF_UNDONE', $m[1]);
			}
			if(preg_match('/(HACK)/i', $this->tokens[$i][1], $m)) {
				$this->report($element, 'WAR_HACK_MARKED');
			}
			if(!in_array($this->tokens[$i][0], array(
				T_DOC_COMMENT, T_NEWLINE, T_WHITESPACE
			)))
				break;
		}
		$i--;
		$element['END'] = $i;
		$element['END_LINE'] = $this->tokens[$i][2];
		$this->elements[] = $element;
		$this->debug("Exiting comment at {$element['END_LINE']}", $depth);
		return ($i == $pos) ? $i : $i-1;
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
				return $this->tokens[$i][0];
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Collect data for elements from subelements
	* @param	$owner	String
	* @param	$data	Mixed
	* @param	$type	int
	----------------------------------------------------------------------+
	*/
	protected function add_data($owner, $data, $type) {
		if($type == T_VARIABLE) {
			if(!isset($this->data[$owner]['THIS']))
				$this->data[$owner]['THIS'] = array();
			if(!in_array($data, $this->data[$owner]['THIS']))
				$this->data[$owner]['THIS'][] = $data;
		} elseif($type == T_METHOD) {
			if(!isset($this->data[$owner]['METHODS']))
				$this->data[$owner]['METHODS'] = array();
			$this->data[$owner]['METHODS'][] = $data;
		}
	}	
	/**
	----------------------------------------------------------------------+
	* @desc 	Write to report
	* @param	$where	String	
	* @param	$what	String
	* @param	$line	Int
	* @param	$extra	Mixed
	----------------------------------------------------------------------+
	*/
	protected function report($element, $what, $extra=null) {
		$report = $this->conf[$what];
		if(isset($report['used']) && $report['used'] === false)
			return;		
		if(!empty($report) && $this->report_on($report['flag'])) {
			$where = isset($element['PARENT']) 
				? $element['PARENT'] : 'COMMENT';
			if(isset($element['NAME'])) $where = $element['NAME'];
			if(isset($report['message_extras'])) {
				$report['message'] = sprintf($report['message_extras'], 
					$extra, $report['compare']);
			} elseif(isset($report['message_extra'])) {
				$report['message'] = sprintf($report['message_extra'], $extra);
			}
			$report['where'] = $where;
			$report['line'] = $element['START_LINE'];
			$this->report[] = $report;
			
			$flag = $report['flag'][0];
			eval('$this->score -= '.$flag.'_PENALTY;');
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Report ?
	* @param	$flag	String
	* @return	Bool
	----------------------------------------------------------------------+
	*/
	private function report_on($flag) {
		switch($flag[0]) {
			case 'C':
				return (!($this->options & OPT_NO_CONVENTION));
			case 'W':
				return (!($this->options & OPT_NO_WARNING));
			case 'R':
				return (!($this->options & OPT_NO_REFACTOR));
			case 'E':
				return (!($this->options & OPT_NO_ERROR));
			case 'I':
				return (!($this->options & OPT_NO_INFORMATION));
			case 'D':
				return (!($this->options & OPT_NO_DEPRICATED));
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
	* @desc 	Find the last newline, i.e. the beginnin00g of the element.
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