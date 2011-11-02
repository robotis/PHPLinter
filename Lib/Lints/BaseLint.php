<?php
/**
----------------------------------------------------------------------+
*  @desc			Base linter.
----------------------------------------------------------------------+
*  @file 			BaseLint.php
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
class BaseLint {
	/* */
	protected $reports;
	/* */
	protected $locals;
	/* */
	protected $element;
	/* */
	protected $conf;
	/* */
	protected $penalty;
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function __construct($element, $config, $options) {
		$this->reports = array();
		$this->locals = array();
		$this->element = $element;
		$this->conf = $config;
		$this->options = $options;
		$this->switch		= false;
		$this->branches		= 0;
		$this->globals = require dirname(__FILE__) . '/../globals.php';
		if($this->report_on('S')) {
			$this->sec_1 = require(dirname(__FILE__) . '/../security/command_exection.php');
			$this->sec_2 = require(dirname(__FILE__) . '/../security/filesystem.php');
			$this->sec_3 = require(dirname(__FILE__) . '/../security/low_risk.php');
			$this->sec_4 = require(dirname(__FILE__) . '/../security/information_disclosure.php');
			$this->sec_5 = require(dirname(__FILE__) . '/../security/accept_callbacks.php');
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
	protected function report($what, $extra=null, $line=null) {
		$report = $this->conf[$what];
		if(!empty($this->use_rules) && !in_array($report['flag'], $this->use_rules)) {
			return;
		}
		if(isset($report['used']) && $report['used'] === false)
			return;		
		if(!empty($report) && $this->report_on($report['flag'])) {
			$where = isset($this->element->parent) 
				? $this->element->parent : 'COMMENT';
			if(isset($this->element->name)) $where = $this->element->name;
			if(isset($report['message_extras'])) {
				$report['message'] = sprintf($report['message_extras'], 
					$extra, $report['compare']);
			} elseif(isset($report['message_extra'])) {
				$report['message'] = sprintf($report['message_extra'], $extra);
			}
			$report['where'] = $where;
			$report['line'] = empty($line) ? $this->element->start_line : $line;
			$this->reports[] = $report;
			
			$flag = $report['flag'][0];
			if(isset($report['penalty']))
				$this->penalty -= $report['penalty'];
			else eval('$this->penalty -= '.$flag.'_PENALTY;');
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
		if($this->options & OPT_ONLY_SECURITY) {
			if($flag[0] == 'S' || in_array($flag, array('I2','I3')))
				return true;
			return false;
		}
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
				return ($this->options & OPT_INFORMATION);
			case 'D':
				return (!($this->options & OPT_NO_DEPRICATED));
			case 'S':
				return (!($this->options & OPT_NO_SECURITY));
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Tokens common to all scopes.
	* @param	$element	Array
	* @param	$pos		int
	----------------------------------------------------------------------+
	*/
	public function common_tokens($pos) {
		$token = $this->element->tokens[$pos];
		switch($token[0]) {
			case T_PUBLIC:
			case T_PRIVATE:
			case T_PROTECTED:
				$this->element->visibility = true;
				break;
			case T_ABSTRACT:
				$this->element->abstract = true;
				break;
			case T_STATIC:
				$this->element->static = true;
				break;
			case T_CLOSE_TAG:
				$this->report('REF_HTML_MIXIN', null, $token[2]);
				break;
			case T_REQUIRE:
			case T_REQUIRE_ONCE:
			case T_INCLUDE:
			case T_INCLUDE_ONCE:
				$n = $pos;
				while(isset($this->element->tokens[++$n]) && $this->element->tokens[$n][0] != T_NEWLINE) {
					if(in_array($this->element->tokens[$n][1], array('$_REQUEST','$_POST','$_GET'))) {
						$this->report('SEC_ERROR_INCLUDE', $token[1], $token[2]);
					}
				}
				break;	
			case T_IS_EQUAL:
			case T_IS_NOT_EQUAL:
				$this->element->start_line = $token[2];
				$this->report('INF_COMPARE');
				break;
			case T_BACKTICK:
				$i = $pos;
				while(true) {
					if(empty($this->element->tokens[++$i])) break;
					$t = $this->element->tokens[$i];
					if($t[0] == T_BACKTICK) break;
					if(in_array($t[1], array('$_REQUEST','$_POST','$_GET'))) {
						$this->report('SEC_ERROR_REQUEST', $token[1], $token[2]);
					}
				}
				break;
			case T_IF:
			case T_ELSE:
			case T_ELSEIF:
			case T_THEN:
				$this->branches++;
				break;
			case T_SWITCH:
				if($this->switch) {
					$this->report('REF_NESTED_SWITCH', null, $token[2]);
				}
				$this->switch = true;
				$this->branches++;
				break;
			default:
				$t = $token[0];
				if(in_array($t, array_keys($this->conf['DPR_DEPRICATED_TOKEN']['compare']))) {
					$this->report('DPR_DEPRICATED_TOKEN',
						$this->conf['DPR_DEPRICATED_TOKEN']['compare'][$t]);		
				}
				break;		
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse a string token
	* @param	$i	int
	----------------------------------------------------------------------+
	*/
	protected function parse_string($pos) {
		$token = $this->element->tokens[$pos];
		$nt = $this->next($pos);
		if($nt === T_PARENTHESIS_OPEN || $nt === T_DOUBLE_COLON) {
			$this->called[] = $token[1];
			$this->security($pos);
		}
		if(in_array($token[1], $this->conf['DPR_DEPRICATED_STRING']['compare'])) {
			$this->report('DPR_DEPRICATED_STRING', $token[1], $token[2]);		
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function penalty() {
		return $this->penalty;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Search for security infractions
	* @param	FIXME
	* @return	FIXME
	----------------------------------------------------------------------+
	*/
	protected function security($at) {
		if($this->report_on('S')) {
			$et = $this->element->tokens;
			$token = $et[$at];
			foreach(array(
					array('sec_1', 'INF_UNSECURE', true),
					array('sec_2', 'INF_UNSECURE', true),
					array('sec_3', 'INF_UNSECURE', false),
					array('sec_4', 'INF_WARNING_DISCLOSURE', false)
				) as $_) {
				if(in_array($token[1], $this->$_[0])) {
					$this->report($_[1], $token[1], $token[2]);
					$i = $at;
					if($_[2]) {
						while($et[++$i][0] != T_PARENTHESIS_CLOSE) {
							if(in_array($et[$i][1], array('$_REQUEST','$_POST','$_GET'))) {
								$this->report('SEC_ERROR_REQUEST', $token[1], $token[2]);
							}
						}
					}
				}
			}
			/* Callbacks */
			if(in_array($token[1], array_keys($this->sec_5))) {
				$this->report('INF_UNSECURE', $token[1], $token[2]);
				foreach($this->sec_5[$token[1]] as $_) {
					$pos = 0;
					$i = $at;
					while($et[++$i][0] != T_PARENTHESIS_CLOSE) {
						if(in_array($et[$i][1], array('$_REQUEST','$_POST','$_GET'))) {
							/* In callback position */
							if($pos == $_) {
								$this->report('SEC_ERROR_CALLBACK', $token[1], $token[2]);
							}
						}
						$pos++;
					}
					/* Last position */
					if(in_array($et[$i-1][1], array('$_REQUEST','$_POST','$_GET')) 
						&& $_ == -1) {
						$this->report('SEC_ERROR_CALLBACK', $token[1], $token[2]);
					}
				}
			}
			/* Special */
			elseif($token[1] == 'preg_replace') {
				// check for '//e' flag
			}
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
		$o = $this->element->tokens;
		while(true) {
			if(!isset($o[$i+1]))
				return false;
			if(Tokenizer::meaningfull($o[++$i][0]))
				return $o[$i][0];
		}
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
		$o = $this->element->tokens;
		while(true) {
			if(!isset($o[$i+1]))
				return false;
			if($o[++$i][0] == $token)
				return $i;
			if(!empty($limit) && ($i - $pos) == $limit)
				return false;
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function lint() {
		if(!empty($this->element->comments)) {
			foreach($this->element->comments as $element) {
				$lint = new Lint_comment($element, $this->conf, $this->options);
				foreach($lint->bind($this)->lint() as $_) $this->reports[] = $_;
				$this->penalty += $lint->penalty();
			}
		}
		$this->recurse();
		return $this->_lint();
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	protected function recurse() {
		if(!empty($this->element->elements)) {
			$a = array(
				T_CLASS 		=> 'Lint_class',
				T_DOC_COMMENT	=> 'Lint_comment',
				T_FUNCTION 		=> 'Lint_function',
				T_INTERFACE 	=> 'Lint_interface',
				T_METHOD 		=> 'Lint_method',
				T_FILE 			=> 'Lint_file',
			);
			$reports = array();
			foreach($this->element->elements as $element) {
				$class = "PHPLinter\\{$a[$element->type]}";
				$lint = new $class($element, $this->conf, $this->options);
				foreach($lint->bind($this)->lint() as $_) $this->reports[] = $_;
				$this->penalty += $lint->penalty();
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Count and process locals at function scope
	* @param	Array
	* @param	Array
	* @param	Array
	* @param	Array
	----------------------------------------------------------------------+
	*/
	protected function process_locals($locals, $_locals, $args) {
		foreach($locals as $ll) {
			// Skip superglobals
			if(in_array($ll, $this->globals)) continue;
			$cnt = count(array_filter($_locals, function($s) use($ll){
					return $s == $ll;
			}));
			if($cnt == 1 && !in_array($ll, $args)) {
				$this->report('WAR_UNUSED_VAR', $ll);
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse argument-list
	* @param	$i		int
	* @param	$et		Array
	* @return	Array
	----------------------------------------------------------------------+
	*/
	protected function parse_args(&$i) {
		$out = array();
		$o = $this->element->tokens;
		while(true) {
			switch($o[++$i][0]) {
				case T_VARIABLE:
					$out[] = $o[$i][1];
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
	* @param	bool
	* @param	Array
	----------------------------------------------------------------------+
	*/
	protected function process_args($locals, $args) {
		if(!empty($args)) {
			foreach($args as $_)
				if(!in_array($_, $locals))
					$this->report('WAR_UNUSED_ARG', $_);	
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	protected function add_parent_data($name, $type) {
		if(empty($this->parent)) return;
		if(!isset($this->parent->locals[$type])) $this->parent->locals[$type] = array();
		if(!in_array($name, $this->parent->locals[$type]))
			$this->parent->locals[$type][] = $name;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function bind(BaseLint & $parent) {
		$this->parent = $parent;
		return $this;
	}
}