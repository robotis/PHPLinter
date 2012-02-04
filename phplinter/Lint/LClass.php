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
		if($this->element->empty) {
			$this->report('WAR_EMPTY_CLASS');
		}
		
		$this->process_tokens();
		
		if(!$this->element->dochead)
			$this->report('DOC_NO_DOCHEAD_CLASS');
			
		$regex = $this->rules['CON_CLASS_NAME']['compare'];
		if(!preg_match($regex, $this->element->name))
			$this->report('CON_CLASS_NAME', $regex);
		
		$len = $this->element->length;
		if($len > $this->rules['REF_CLASS_LENGTH']['compare'])
			$this->report('REF_CLASS_LENGTH', $len);	
		
		if(!empty($this->locals[T_METHOD]) && 
			in_array($this->element->name, $this->locals[T_METHOD]))
			$this->report('WAR_OLD_STYLE_CONSTRUCT');
			
		if(!empty($this->element->elements)) {
			$static = array(0,0);
			foreach($this->element->elements as $_) {
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
		$tcnt 		= $this->element->token_count;
		$et 		= $this->element->tokens;
		$locals 	= array();
		$methods 	= 0;
		$comment 	= false;
		for($i = 0;$i < $tcnt;$i++) {
			$token = $et[$i];
			switch($token[0]) {
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
		foreach($compares as $k => $_)
			if($_ > $this->rules[$k]['compare'])
				$this->report($k, $_);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process locals at class scope
	----------------------------------------------------------------------+
	*/
	protected function process_locals($locals) {
		$locals = array_unique($locals);
		if(empty($this->locals[T_VARIABLE])) 
			$this->locals[T_VARIABLE] = array();
		$vars = array_diff($locals, $this->locals[T_VARIABLE]);
		foreach($vars as $_) {
			$this->report('WAR_UNUSED_PROPERTY', $_);	
		}
		$udef = array_diff($this->locals[T_VARIABLE], $locals);
		foreach($udef as $_) {
			$this->report('CON_PROPERTY_DEFINED_IN_METHOD', $_);	
		}
		return count($udef) + count($locals);
	} 
}