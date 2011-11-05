<?php
/**
----------------------------------------------------------------------+
*  @desc			Lint a method.
----------------------------------------------------------------------+
*  @file 			Lint_method.php
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
namespace PHPLinter;
class Lint_method extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	analyse element
	* @return 	Array	Reports
	----------------------------------------------------------------------+
	*/
	public function _lint() {
		$this->add_parent_data($this->element->name, T_METHOD);
		
		$this->process_tokens();

		if($this->element->empty && !$this->element->abstract) 
			$this->report('WAR_EMPTY_METHOD');
			
		if(!$this->element->dochead)
			$this->report('DOC_NO_DOCHEAD_METHOD');
			
		if(!$this->element->visibility)
			$this->report('CON_NO_VISIBILITY');
			
		$regex = $this->rules['CON_METHOD_NAME']['compare'];
		if(!(substr($this->element->name, 0, 2) == '__') 
			&& !preg_match($regex, $this->element->name))
			$this->report('CON_METHOD_NAME', $regex);
			
		return $this->reports;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process token array
	----------------------------------------------------------------------+
	*/
	protected function process_tokens() {
		$tcnt 		= $this->element->token_count;
		$et 		= $this->element->tokens;
		$args		= false;
		$_locals 	= array();
		
		for($i = 0;$i < $tcnt;$i++) {
			switch($et[$i][0]) {
				case T_PARENTHESIS_OPEN:
					if($args === false) {
						$args = $this->parse_args($i);
					}
					break;
				case T_VARIABLE:
					if($et[$i][1] == '$this') {
						$this->parent_local($i);
					} else {
						$_locals[] = $et[$i][1];
					}
					break;
				default:
					$this->common_tokens($i);
					break;
			}
		}
		$locals = array_unique($_locals);
		$compares = array(
			'REF_ARGUMENTS' => count($args),
			'REF_LOCALS' => count($locals),
			'REF_BRANCHES' => $this->branches,
			'REF_METHOD_LENGTH' => $this->element->length
		);
		
		foreach($compares as $k => $_)
			if($_ > $this->rules[$k]['compare'])
				$this->report($k, $_);
		if(!$this->element->abstract)
			$this->process_args($locals, $args);
		$this->process_locals($locals, $_locals, $args);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process $this token
	----------------------------------------------------------------------+
	*/
	protected function parent_local(&$pos) {
		$o = $this->element->tokens;
		$j = $this->find($pos, T_STRING);
		if($j !== false) {
			$k = $o[$this->next($j)][0];
			if($k === T_PARENTHESIS_OPEN)
				$this->add_parent_data($o[$j][1], T_METHOD);
			else
				$this->add_parent_data($o[$j][1], T_VARIABLE);
			$pos = $j;
		}
	}
}