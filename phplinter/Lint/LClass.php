<?php
/**
----------------------------------------------------------------------+
*  @desc			Lint a class.
----------------------------------------------------------------------+
*  @file 			Lint_class.php
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
class LClass extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	Analyze class
	----------------------------------------------------------------------+
	*/
	public function _lint() {
		if($this->node->empty) {
			$this->report('WAR_EMPTY_CLASS');
		}
		
		$this->process_tokens();
		
		if(!$this->node->dochead)
			$this->report('DOC_NO_DOCHEAD_CLASS');
			
		if($this->config->match_rule('CON_CLASS_NAME', $this->node->name)) {
			$this->report('CON_CLASS_NAME', $this->node->name);
		}
		$len = $this->node->length;
		if($this->config->match_rule('REF_CLASS_LENGTH', $len)) {
			$this->report('REF_CLASS_LENGTH', $len);
		}
		
		if(!empty($this->locals[T_METHOD]) && 
			in_array($this->node->name, $this->locals[T_METHOD]))
			$this->report('WAR_OLD_STYLE_CONSTRUCT');
			
		if(!empty($this->node->nodes)) {
			$static = array(0,0);
			foreach($this->node->nodes as $_) {
				if(isset($_->static) && $_->static) {
					$static[0]++;
				} else {
					$static[1]++;
				}
			}
			if($static[0] > 0 && $static[1] > 0) {
				$this->report('REF_STATIC_MIX');
			}
		}
		
		return $this->reports;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process tokenstream
	----------------------------------------------------------------------+
	*/
	protected function process_tokens() {
		$tcnt 		= $this->node->token_count;
		$et 		= $this->node->tokens;
		$locals 	= array();
		$methods 	= 0;
		$comment 	= false;
		for($i = 0;$i < $tcnt;$i++) {
			$token = $et[$i];
			switch($token[0]) {
				case T_EXTENDS:
					$n = $this->next($i);
					$this->node->extends = $et[$n][1];
					break;
				case T_IMPLEMENTS:
					$n = $this->next($i);
					$this->node->implements = $et[$n][1];
					break;
				case T_COMMENT:
				case T_DOC_COMMENT:
					$comment = true;
					break;
				case T_VAR:
					$this->report('WAR_OLD_STYLE_VARIABLE', null, $token[2]);
					break;
				case T_VARIABLE:
					if(!$comment) {
						$this->report('DOC_NO_DOCHEAD_PROPERTY', $token[1], $token[2]);
					}
					$comment = false;
					if($methods > 0)
						$this->report('CON_MISPLACED_PROPERTY');
					$locals[] = substr($token[1], 1);
					break;
				case T_FUNCTION:
					$comment = false;
					$methods++;
					break;
				default:
					$this->common_tokens($i);
					break;
			}
		}
		$lcnt = $this->process_locals($locals);
		$compares = array(
			'REF_CLASS_METHODS' => $methods,
			'REF_CLASS_PROPERTYS' => $lcnt,
		);
		foreach($compares as $k => $_) {
			if($this->config->match_rule($k, $_)) {
				$this->report($k, $_);
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process locals at class scope
	----------------------------------------------------------------------+
	*/
	protected function process_locals($locals) {
		$locals = array_unique($locals);
		$_mlocals = array();
		if(!empty($this->locals[T_VARIABLE])) {
			foreach($this->locals[T_VARIABLE] as $_) {
				if($p = mb_strpos($_, '::')) {
					$locals[] = mb_substr($_, $p+2);
				} else {
					$_mlocals[] = $_;
				}
			}
		}
		$vars = array_diff($locals, $_mlocals);
		foreach($vars as $_) {
			$this->report('WAR_UNUSED_PROPERTY', $_);	
		}
		$udef = array_diff($_mlocals, $locals);
		foreach($udef as $_) {
			$this->report('CON_PROPERTY_DEFINED_IN_METHOD', $_);	
		}
		return count($udef) + count($locals);
	} 
}