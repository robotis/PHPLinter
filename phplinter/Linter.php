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
namespace phplinter;
require_once dirname(__FILE__) . '/constants.php';
/**
----------------------------------------------------------------------+
* @desc 	Linter. Measures code and splits into nodes.
----------------------------------------------------------------------+
*/
class Linter {
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
	* @param	object	Config object
	----------------------------------------------------------------------+
	*/
	public function __construct($file, Config $config) {
		$this->config 	= $config;
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
			
			if(isset($override) && is_array($override) && !empty($override)) {
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
			$node = $this->measure_file();
			$lint = new Lint\LFile($node, $this->rules, $this->config);
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
		$node 				= new Lint\Node();
		$node->type 		= T_FILE;
		$node->file	 		= $this->file;
		$node->parent		= $this->file;
		$node->name 		= $this->file;
		$node->owner 		= $this->file;
		$node->tokens 		= array();
		$node->depth 		= 0;
		$node 				= $this->measure(0, $node, $i);
		$node->end_line 	= $this->tokens[$i][2];
		$node->length 		= ($node->end_line - $node->start_line);
		$node->token_count 	= count($node->tokens);
		$this->profile('measure_file::' . $this->file);
		return $node;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Split token stream into nodes of type function, comment,
	* 			class or method.
	* @param	int			Start position
	* @param	Object		Node
	* @param	int			Depth
	* @return	int
	----------------------------------------------------------------------+
	*/
	protected function measure($pos, Lint\Node $node, &$ret) { 
		$start = $this->last_newline($pos);
		$node->start = $start;

		if($node->type === T_FILE) {
			$node->start_line = 1;
			$node->empty = false;
		} else {
			$node->start_line = $this->tokens[$start][2] + 1;
			$node->empty = true;
		}

		$this->debug(sprintf('In Node `%s` of type `%s` line %d; Owned by `%s`'
							 ,$node->name
							 ,Tokenizer::token_name($node->type)
							 ,$node->start_line
							 ,$node->owner
					), $node->depth, OPT_SCOPE_MAP, true);
				
		$this->scope = array();
		
		$next_node = new Lint\Node();
		$tokens = $this->tokens;
		for($i = $pos; $i < $this->tcount; $i++) {
			if(count($this->scope) > 0 
				&& $node->empty
			    && Tokenizer::meaningfull($tokens[$i][0]) 
			    && $tokens[$i][0] !== T_CURLY_CLOSE) 
			{
				$node->empty = false;
			}
			if(!empty($this->ignore_next) 
				&& $tokens[$i][0] === $this->ignore_next[count($this->ignore_next) - 1]) 
			{
				$node->tokens[] = $tokens[$i];
				array_pop($this->ignore_next);
				continue;
			}
			switch($tokens[$i][0]) {
				case T_INLINE_HTML:
					// Gather inline html into one token
					$node->tokens[] = $tokens[$i];
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
					$this->open_scope($i, $node);
					break;
				case T_IF:
				case T_ELSEIF:
				case T_THEN:
				case T_FOREACH:
				case T_WHILE:
				case T_SWITCH:
					$this->open_scope($i, $node);
					break;
				case T_FOR:
					$this->open_scope($i, $node);
					$this->ignore_next[] = T_SEMICOLON;
					$this->ignore_next[] = T_SEMICOLON;
					break;
				case T_BASIC_CURLY_OPEN:
					$this->open_scope($i, $node);
					break;
				case T_SEMICOLON:
					$this->close_scope($i, $node);
					$node->tokens[] = $tokens[$i];
					if(empty($this->scope) && $node->empty) {
						$i++;
						break 2;
					} 
					break;
				case T_CURLY_CLOSE:
					$this->close_scope($i, $node);
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
					$next_node->visibility = Tokenizer::token_name($tokens[$i][0]);
					break;
				case T_ABSTRACT:
					$next_node->abstract = true;
					break;
				case T_STATIC:
					$next_node->static = true;
					break;
				case T_COMMENT:
					$node->tokens[] = $tokens[$i];
					$node->comments[] = $this->measure_comment($i, $node->depth, $i);
					break;
				case T_DOC_COMMENT:
					$node->tokens[] = $tokens[$i];
					$next_node->comments[] = $this->measure_comment($i, $node->depth, $i);
					break;
				case T_CLASS:
				case T_INTERFACE:
				case T_FUNCTION:
					list($type, $name, $owner) = $this->determine_type($i, $node, $next_node);
					$next_node->type = $type;
					$next_node->name = $name;
					$next_node->depth = $node->depth + 1;
					$next_node->owner = $owner;
					$node->tokens[] = $tokens[$i];
					// preserve scope
					$scope = $this->scope;
					$node->nodes[] = $this->measure($i+1, $next_node, $i);
					$this->scope = $scope;
					$next_node = new Lint\Node();
					break;
				default:
					$node->tokens[] = $tokens[$i];
					break;
			}
		}
		// In case $i is over the buffer
		$node->end = ($i >= $this->tcount)
			? --$i : $i;
			
		$node->end_line = $tokens[$i][2];
		$node->length = ($node->end_line - $node->start_line);
		$node->token_count = count($node->tokens);
		$ret = ($i > 0) ? --$i : $i;
		$this->debug(sprintf('Exiting Node `%s` of type `%s` line %d'
							 ,$node->name
							 ,Tokenizer::token_name($node->type)
							 ,$node->end_line
					 ), $node->depth, OPT_SCOPE_MAP, true);
		return $node;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Determine type of new node
	* @param 	int			Position
	* @param	Object		Current Node
	* @param	Object		Next Node
	* @return   Array
	----------------------------------------------------------------------+
	*/
	protected function determine_type($pos, Lint\Node $node, Lint\Node &$next_node) {
		$tokens = $this->tokens;
		$next = $this->find($pos, array(T_STRING, T_PARENTHESIS_OPEN));
		$type = $tokens[$pos][0];
		if($next === false || $tokens[$next][0] === T_PARENTHESIS_OPEN) {
			// anonymous functions
			$name = 'anonymous';
			$type = T_ANON_FUNCTION;
		} else {
			$name = $tokens[$next][1];
			if(in_array($node->type, array(T_CLASS, T_INTERFACE)) 
				&& $tokens[$pos][0] == T_FUNCTION) 
			{
				$type = T_METHOD;
				if($node->type === T_INTERFACE) {
					$next_node->abstract = true;
				}
			}
		}
		if($type === T_METHOD || $type === T_ANON_FUNCTION) {
			$owner = $node->name;
		} else $owner = $node->owner;
		return array($type, $name, $owner);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Open scope, set scope token
	----------------------------------------------------------------------+
	*/
	protected function open_scope($pos, Lint\Node $node) {
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
			$depth = count($this->scope) + $node->depth;
			$this->debug(sprintf('Scope opened by `%s` line %d'
								 ,$token[1]
								 ,$token[2])
						,$depth, OPT_SCOPE_MAP, true);
			$this->scope[] = $token;
			$node->tokens[] = array(T_OPEN_SCOPE, $token[1], $token[2]);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Close scope, set scope token
	----------------------------------------------------------------------+
	*/
	protected function close_scope($pos, Lint\Node $node) {
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
					$depth = count($this->scope) + $node->depth;
					$this->debug(sprintf('Scope closed by `%s` line %d'
										 ,$token[1]
										 ,$token[2])
								,$depth, OPT_SCOPE_MAP, true);
					$node->tokens[] = array(T_CLOSE_SCOPE, $token[1], $token[2]);
				}
			}
		} else {
			$depth = count($this->scope) + $node->depth;
			$this->debug(sprintf('Scope closed by `%s` line %d'
								 ,$token[1]
								 ,$token[2])
						,$depth, OPT_SCOPE_MAP, true);
			array_pop($this->scope);
			$node->tokens[] = array(T_CLOSE_SCOPE, $token[1], $token[2]);
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
		if($this->config->check($mode)) {
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
		$node = new Lint\Node();
		$node->start = $pos;
		$node->start_line = $this->tokens[$pos][2];
		$node->type = $this->tokens[$pos][0];
		$node->name = 'comment';
		$depth += count($this->scope);
		
		$this->debug("In comment at {$node->start_line}", $depth, OPT_SCOPE_MAP, true);
		for($i = $pos;$i < $this->tcount;$i++) {
			if(Tokenizer::meaningfull($this->tokens[$i][0])) {
				$i--;
				break;
			}
			$node->tokens[] = $this->tokens[$i];
		}
		if($i === $this->tcount) $i--;
		$node->end = $i;
		$node->end_line = $this->tokens[$i][2];
		$this->debug("Exiting comment at {$node->end_line}", $depth, OPT_SCOPE_MAP, true);
		$ret = $i;
		return $node;
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