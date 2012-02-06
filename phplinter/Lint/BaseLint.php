<?php
/**
----------------------------------------------------------------------+
*  @desc			Base linter.
----------------------------------------------------------------------+
*  @file 			BaseLint.php
*  @author 			Jóhann T. Maríusson <jtm@robot.is>
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
namespace phplinter\Lint;
class BaseLint {
	/* @var Array */
	protected $reports;
	/* @var Array */
	protected $locals;
	/* @var Object */
	protected $node;
	/* @var Array */
	protected $rules;
	/* @var Float */
	protected $penalty;
	/**
	----------------------------------------------------------------------+
	* @desc 	__construct
	* @param	Object 	Element Object
	* @param	Array	Rule set
	* @param	Int		Option flags
	----------------------------------------------------------------------+
	*/
	public function __construct($node, $rules, $config) {
		$this->reports 	= array();
		$this->locals 	= array();
		$this->node 	= $node;
		$this->rules 	= $rules;
		$this->config 	= $config;
		$this->scope	= -1;
		$this->branches	= -1;
		$this->switch	= false;
		$this->final_return = false;
		$dir = dirname(__FILE__);
		$this->globals 	= require $dir . '/../globals.php';
		$this->uvars	= require $dir . '/../uservars.php';
		if($this->report_on('S')) {
			$this->sec_1 = require($dir . '/../Security/command_exection.php');
			$this->sec_2 = require($dir . '/../Security/filesystem.php');
			$this->sec_3 = require($dir . '/../Security/low_risk.php');
			$this->sec_4 = require($dir . '/../Security/information_disclosure.php');
			$this->sec_5 = require($dir . '/../Security/accept_callbacks.php');
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Write to report.
	* @param	String	Flag	
	* @param	Mixed	Extra option
	* @param	int		Line number
	----------------------------------------------------------------------+
	*/
	protected function report($what, $extra=null, $line=null) {
		$report = $this->rules[$what];
		if(!empty($this->use_rules) && !in_array($report['flag'], $this->use_rules)) {
			return;
		}
		if(isset($report['used']) && $report['used'] === false)
			return;		
		if(!empty($report) && $this->report_on($report['flag'])) {
			$where = isset($this->node->parent) 
				? $this->node->parent : 'COMMENT';
			if(isset($this->node->name)) $where = $this->node->name;
			if(isset($report['message_extras'])) {
				$report['message'] = sprintf($report['message_extras'], 
					$extra, $report['compare']);
			} elseif(isset($report['message_extra'])) {
				$report['message'] = sprintf($report['message_extra'], $extra);
			}
			$report['where'] = $where;
			$report['line'] = empty($line) ? $this->node->start_line : $line;
			$this->reports[] = $report;
			
			$flag = $report['flag'][0];
			if(isset($report['penalty']))
				$this->penalty -= $report['penalty'];
			else eval('$this->penalty -= '.$flag.'_PENALTY;');
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Report on node ?
	* @param	String	Flag
	* @return	Bool
	----------------------------------------------------------------------+
	*/
	protected function report_on($flag) {
		if($this->config->check(OPT_ONLY_SECURITY)) {
			if($flag[0] == 'S' || in_array($flag, array('I2','I3')))
				return true;
			return false;
		}
		switch($flag[0]) {
			case 'C':
				return (!($this->config->check(OPT_NO_CONVENTION)));
			case 'W':
				return (!($this->config->check(OPT_NO_WARNING)));
			case 'R':
				return (!($this->config->check(OPT_NO_REFACTOR)));
			case 'E':
				return (!($this->config->check(OPT_NO_ERROR)));
			case 'I':
				return (!($this->config->check(OPT_NO_INFORMATION)));
			case 'D':
				return (!($this->config->check(OPT_NO_DEPRICATED)));
			case 'S':
				return (!($this->config->check(OPT_NO_SECURITY)));
			case 'F':
				return (!($this->config->check(OPT_NO_FORMATTING)));
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Tokens common to all scopes.
	* @param	int	current position
	----------------------------------------------------------------------+
	*/
	public function common_tokens($pos) {
		$token = $this->node->tokens[$pos];
		if($this->final_return === 1 
			&& $token[0] !== T_CLOSE_SCOPE 
			&& \phplinter\Tokenizer::meaningfull($token[0])) 
		{
			$this->final_return = 2;
			$this->report('WAR_UNREACHABLE_CODE', null, $token[2]);
		}
		$t = $token[0];
		if(in_array($t, array_keys($this->rules['WAR_DEPRICATED_TOKEN']['compare']))) 
		{
			$this->report('WAR_DEPRICATED_TOKEN',
			$this->rules['WAR_DEPRICATED_TOKEN']['compare'][$t]);		
		}
		switch($t) {
			case T_INLINE_HTML:
				$this->report('REF_HTML_MIXIN', null, $token[2]);
				break;
			case T_REQUIRE:
			case T_REQUIRE_ONCE:
			case T_INCLUDE:
			case T_INCLUDE_ONCE:
				$this->sec_includes($pos);
				break;	
			case T_IS_EQUAL:
			case T_IS_NOT_EQUAL:
				$this->report('INF_COMPARE', null, $token[2]);
				break;
			case T_BACKTICK:
				$this->sec_backtick($pos);
				break;
			case T_STRING:
				$this->parse_string($pos);
				break;
			case T_RETURN:
			case T_EXIT:
				$prev = $this->node->tokens[$this->prev($pos)];
				if(!in_array($prev[0], array(T_LOGICAL_OR, T_BOOLEAN_OR)) 
					&& $this->scope === 0 
					&& $this->final_return === false) 
				{
					$this->final_return = true;
				}
				break;
			case T_OPEN_SCOPE:
				$this->branches++;
				if($token[1] === 'switch') {
					if($this->switch !== false) {
						$this->report('REF_NESTED_SWITCH', null, $token[2]);
					}
					$this->switch = $this->scope;
				}
				$this->scope++;
				if(($this->scope) > $this->rules['REF_DEEP_NESTING']['compare'])
					$this->report('REF_DEEP_NESTING', $this->scope, $token[2]);
				break;
			case T_CLOSE_SCOPE:
				$this->scope--;
				if($this->switch === $this->scope)
					$this->switch = false;
				break;
			case T_SEMICOLON:
				if($this->final_return === true) {
					$this->final_return = 1;
				}
				break;		
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Penalty
	* @return 	Float
	----------------------------------------------------------------------+
	*/
	public function penalty() {
		return $this->penalty;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Search for security infractions
	* @param	int	current position
	----------------------------------------------------------------------+
	*/
	protected function security($pos) {
		if($this->report_on('S')) {
			$token = $this->node->tokens[$pos];
			$this->sec_strings($pos);
			if(in_array($token[1], array_keys($this->sec_5))) {
				$this->sec_callbacks($pos);
			}
			/* Special */
			elseif($token[1] == 'preg_replace') {
				// check for '//e' flag
				
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Search for security infractions in callback positions
	* @param	int
	----------------------------------------------------------------------+
	*/
	protected function sec_callbacks($pos) {
		$o = $this->node->tokens;
		$t = $o[$pos];
		$this->report('INF_UNSECURE', $t[1], $t[2]);
		foreach($this->sec_5[$t[1]] as $_) {
			$p = 0;
			$i = $pos;
			while($o[++$i][0] != T_PARENTHESIS_CLOSE) {
				if(in_array($o[$i][1], $this->uvars)) {
					/* In callback position */
					if($p == $_) {
						$this->report('SEC_ERROR_CALLBACK', $t[1], $t[2]);
					}
				}
				$p++;
			}
			/* Last position */
			if(in_array($o[$i-1][1], $this->uvars) 
				&& $_ == -1) {
				$this->report('SEC_ERROR_CALLBACK', $t[1], $t[2]);
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Search for security infractions in strings
	* @param	int
	----------------------------------------------------------------------+
	*/
	protected function sec_strings($pos) {
		$o = $this->node->tokens;
		$t = $o[$pos];
		foreach(array(
					array('sec_1', 'INF_UNSECURE', true),
					array('sec_2', 'INF_UNSECURE', true),
					array('sec_3', 'INF_UNSECURE', false),
					array('sec_4', 'INF_WARNING_DISCLOSURE', false)
				) as $_) {
			if(in_array($t[1], $this->$_[0])) {
				$this->report($_[1], $t[1], $t[2]);
				$i = $pos;
				if($_[2]) {
					while($o[++$i][0] != T_PARENTHESIS_CLOSE) {
						if(in_array($o[$i][1], $this->uvars)) {
							$this->report('SEC_ERROR_REQUEST', $t[1], $t[2]);
						}
					}
				}
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Search for security infractions in includes
	* @param	int
	----------------------------------------------------------------------+
	*/
	protected function sec_includes($pos) {
		$i = $pos;
		$o = $this->node->tokens;
		while(isset($o[++$i]) && $o[$i][0] !== T_SEMICOLON) {
			if(in_array($o[$i][1], $this->uvars)) {
				$this->report('SEC_ERROR_INCLUDE', $o[$pos][1], $o[$pos][2]);
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Search for security infractions in backticks
	* @param	int
	----------------------------------------------------------------------+
	*/
	protected function sec_backtick($pos) {
		$i = $pos;
		$o = $this->node->tokens;
		while(true) {
			if(empty($o[++$i])) break;
			$t = $o[$i];
			if($t[0] === T_BACKTICK || $t[0] === T_SEMICOLON) break;
			if(in_array($t[1], $this->uvars)) {
				$this->report('SEC_ERROR_REQUEST', $o[$pos][1], $o[$pos][2]);
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse a string token
	* @param	int	current position
	----------------------------------------------------------------------+
	*/
	protected function parse_string($pos) {
		$token = $this->node->tokens[$pos];
		$nt = $this->node->tokens[$this->next($pos)][0];
		if($nt === T_PARENTHESIS_OPEN || $nt === T_DOUBLE_COLON) {
			$this->security($pos);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Return the next meaningfull token
	* @param	int	current position
	* @return	Int
	----------------------------------------------------------------------+
	*/
	protected function next($pos) {
		$i = $pos;
		$o = $this->node->tokens;
		$c = $this->node->token_count;
		while(++$i < $c) {
			if(\phplinter\Tokenizer::meaningfull($o[$i][0]))
				return $i;
		}
		return false;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Return the previous meaningfull token
	* @param	int	current position
	* @return	Int
	----------------------------------------------------------------------+
	*/
	protected function prev($pos) {
		$i = $pos;
		$o = $this->node->tokens;
		$c = $this->node->start;
		while(--$i > $c) {
			if(\phplinter\Tokenizer::meaningfull($o[$i][0]))
				return $i;
		}
		return false;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Find the next token.
	* @param	int		current position
	* @param	int		Seek token
	* @param	int		Limit to forward track
	* @return 	Int
	----------------------------------------------------------------------+
	*/
	protected function find($pos, $token, $limit=10) {
		$i = $pos;
		$o = $this->node->tokens;
		$c = $this->node->token_count;
		while(++$i < $c) {
			if($o[$i][0] == $token)
				return $i;
			if(!empty($limit) && ($i - $pos) == $limit)
				break;
		}
		return false;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Analyse node
	* @return 	Array	Reports
	----------------------------------------------------------------------+
	*/
	public function lint() {
		$this->node->dochead = false;
		if(!empty($this->node->comments)) {
			foreach($this->node->comments as $node) {
				if($node->type === T_DOC_COMMENT) $this->node->dochead = true;
				$node->owner = $this->node->name;
				$lint = new LComment($node, $this->rules, $this->config);
				foreach($lint->bind($this)->lint() as $_) {
					$this->reports[] = $_;
				}
				$this->penalty += $lint->penalty();
			}
		}
		$this->recurse();
		return $this->_lint();
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Analyse nodes owned by current node
	----------------------------------------------------------------------+
	*/
	protected function recurse() {
		if(!empty($this->node->nodes)) {
			$a = array(
				T_CLASS 		=> 'LClass',
				T_DOC_COMMENT	=> 'LComment',
				T_FUNCTION 		=> 'LFunction',
				T_ANON_FUNCTION => 'LAnon_function',
				T_INTERFACE 	=> 'LInterface',
				T_METHOD 		=> 'LMethod',
				T_FILE 			=> 'LFile',
			);
			foreach($this->node->nodes as $node) {
				$this->profile();
				$class = "\\phplinter\\Lint\\{$a[$node->type]}";
				$lint = new $class($node, $this->rules, $this->config);
				foreach($lint->bind($this)->lint() as $_) $this->reports[] = $_;
				$this->penalty += $lint->penalty();
				unset($lint);
				$this->profile($class.'::'.$node->name);
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
	* @desc 	Count and process locals at function scope
	* @param	Array
	* @param	Array	
	* @param	Array
	----------------------------------------------------------------------+
	*/
	protected function process_locals($locals, $_locals, $args) {
		foreach($locals as $ll) {
			// Skip superglobals
			if(in_array($ll, $this->globals)) continue;
			$cnt = count(array_filter($_locals, function($s) use($ll) {
					return $s == $ll;
			}));
			if($cnt == 1 && !in_array($ll, $args)) {
				if(isset($this->locals[T_VARIABLE]) 
					&& !in_array($ll, $this->locals[T_VARIABLE]))
				{
					$this->report('WAR_UNUSED_VAR', $ll);
				}
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse argument-list
	* @param	int	current position
	* @return	Array
	----------------------------------------------------------------------+
	*/
	protected function parse_args(&$i) {
		$out = array();
		$o = $this->node->tokens;
		while(true) {
			switch($o[++$i][0]) {
				case T_VARIABLE:
					if($o[$i+1][0] === T_EQUALS) {
						$out[$o[$i][1]] = $o[$i+2][1];
						$i += 2;
					} else {
						$out[$o[$i][1]] = null;
					}
					break;
				case T_PARENTHESIS_CLOSE:
					return $out;
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process argument list to function
	* @param	Array
	* @param	Array
	----------------------------------------------------------------------+
	*/
	protected function process_args($locals, $args) {
		if(!empty($args)) {
			foreach(array_keys($args) as $_) 
				if(!in_array($_, $locals))
					$this->report('WAR_UNUSED_ARG', $_);	
		}
		$this->node->arguments = $args;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Add data to node parent
	* @param	String	Name
	* @param	int		Type
	----------------------------------------------------------------------+
	*/
	protected function add_parent_data($name, $type) {
		if(empty($this->parent)) return;
		if(!isset($this->parent->locals[$type])) $this->parent->locals[$type] = array();
		if(is_array($name)) {
			$this->parent->locals[$type] = array_merge($name, $this->parent->locals[$type]);
		}
		elseif(!in_array($name, $this->parent->locals[$type]))
			$this->parent->locals[$type][] = $name;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Bind parent node to current node
	* @param	Object		BaseLint
	* @return 	this
	----------------------------------------------------------------------+
	*/
	public function bind(BaseLint & $parent) {
		$this->parent = $parent;
		return $this;
	}
}