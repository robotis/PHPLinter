<?php
/**
----------------------------------------------------------------------+
*  @desc			Lint an anonymous function.
----------------------------------------------------------------------+
*  @file 			Lint_function.php
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
class Lint_anon_function extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	Analyze
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public function _lint() {
		$tcnt 		= count($this->element->tokens);
		$et 		= $this->element->tokens;
		$args		= false;
		$_locals 	= array();
		for($i = 0;$i < $tcnt;$i++) {
			switch($et[$i][0]) {
				case T_PARENTHESIS_OPEN:
					if($args === false) {
						$args = $this->parse_args($i);
						if(count($args) > $this->rules['REF_ARGUMENTS']['compare'])
							$this->report('REF_ARGUMENTS', count($args));
					}
					break;
				case T_VARIABLE:
					$_locals[] = $et[$i][1];
					break;
				case T_USE: 
					$i = $this->find($i, T_PARENTHESIS_OPEN);
					$_args = $this->parse_args($i);
					$this->add_parent_data($_args, T_VARIABLE);
					if(count($_args) > $this->rules['REF_USE_ARGUMENTS']['compare'])
						$this->report('REF_USE_ARGUMENTS', count($_args));
					$args = array_merge($args, $_args);
					break;
				default:
					$this->common_tokens($i);
					break;
			}
		}
		$locals = array_unique($_locals);
		$compares = array(
			'REF_LOCALS' => count($locals),
			'REF_BRANCHES' => $this->branches,
			'REF_FUNCTION_LENGTH' => $this->element->length
		);
		foreach($compares as $k => $_)
			if($_ > $this->rules[$k]['compare'])
				$this->report($k, $_);
				
		$this->process_args($locals, $args);	
		$this->process_locals($locals, $_locals, $args);
		
		return $this->reports;
	}
}