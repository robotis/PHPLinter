<?php
/**
----------------------------------------------------------------------+
*  @desc			Lint a class.
----------------------------------------------------------------------+
*  @file 			Lint_class.php
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
class Lint_class extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function _lint() {
//		echo "Lint_class::lint - {$this->element->name}\n";
		if($this->element->empty) {
			$this->report('WAR_EMPTY_CLASS');
		}
		
		$this->process_tokens();
		
		if(empty($this->element->comments))
			$this->report('ERR_NO_DOCHEAD_CLASS');
			
		$regex = $this->conf['CON_CLASS_NAME']['compare'];
		if(!preg_match($regex, $this->element->name))
			$this->report('CON_CLASS_NAME', $regex);
		
		$len = $this->element->length;
		if($len > $this->conf['REF_CLASS_LENGTH']['compare'])
			$this->report('REF_CLASS_LENGTH', $len);	
		
		if(!empty($this->locals[T_METHOD]) && 
			in_array($this->element->name, $this->locals[T_METHOD]))
			$this->report('WAR_OLD_STYLE_CONSTRUCT');
			
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
		
		return $this->reports;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
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
//			echo Tokenizer::token_name($token[0]) . "\n";
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
						$this->report('ERR_NO_DOCHEAD_PROPERTY', $token[1], $token[2]);
					}
					$comment = false;
					if($methods > 0)
						$this->report('CON_MISPLACED_PROPERTY');
					$locals[] = substr($token[1], 1);
					break;
				case T_STRING:
					$this->parse_string($i);
					break;
				case T_METHOD:
					$comment = false;
					$methods++;
					break;
				default:
					$this->common_tokens($i);
					break;
			}
		}
		if(empty($this->locals[T_VARIABLE])) 
			$this->locals[T_VARIABLE] = array();
		$vars = array_diff($locals, $this->locals[T_VARIABLE]);
		foreach($vars as $_) {
			$this->report('WAR_UNUSED_PROPERTY', $_);	
		}
	}
}