<?php
/**
----------------------------------------------------------------------+
*  @desc			Lint a function.
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
class Lint_function extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	Analyze function
	----------------------------------------------------------------------+
	*/
	public function _lint() {
		if($this->element->empty) 
			$this->report('WAR_EMPTY_FUNCTION');
			
		if(!$this->element->dochead)
			$this->report('DOC_NO_DOCHEAD_FUNCTION');
			
		$this->process_tokens();
		
		$regex = $this->rules['CON_FUNCTION_NAME']['compare'];
		if(!(substr($this->element->name, 0, 2) == '__') 
			&& !preg_match($regex, $this->element->name))
			$this->report('CON_FUNCTION_NAME', $regex);
			
		return $this->reports;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process tokenstream
	----------------------------------------------------------------------+
	*/
	protected function process_tokens() {
		$tcnt 		= count($this->element->tokens);
		$et 		= $this->element->tokens;
		$args		= false;
		$_locals 	= array();
		$branches 	= 0;
		for($i = 0;$i < $tcnt;$i++) {
			switch($et[$i][0]) {
				case T_PARENTHESIS_OPEN:
					if($args === false) {
						$args = $this->parse_args($i);
					}
					break;
				case T_VARIABLE:
					$_locals[] = $et[$i][1];
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
			'REF_FUNCTION_LENGTH' => $this->element->length
		);
		foreach($compares as $k => $_)
			if($_ > $this->rules[$k]['compare'])
				$this->report($k, $_);
				
		$this->process_args($locals, $args);	
		$this->process_locals($locals, $_locals, $args);
	}
}

