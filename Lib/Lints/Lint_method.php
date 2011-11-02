<?php
/**
----------------------------------------------------------------------+
*  @desc			Lint a method.
----------------------------------------------------------------------+
*  @file 			Lint_method.php
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
class Lint_method extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function _lint() {
//		echo "Lint_method::lint - {$this->element->name}\n";
		$this->add_parent_data($this->element->name, T_METHOD);
		
		$this->process_tokens();
		
		if($this->element->empty) 
			$this->report('WAR_EMPTY_METHOD');
			
		if(empty($this->element->comments))
			$this->report('ERR_NO_DOCHEAD_METHOD');
			
		if(!$this->element->visibility)
			$this->report('CON_NO_VISIBILITY');
			
		$regex = $this->conf['CON_METHOD_NAME']['compare'];
		if(!(substr($this->element->name, 0, 2) == '__') 
			&& !preg_match($regex, $this->element->name))
			$this->report('CON_METHOD_NAME', $regex);
			
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
		$args		= false;
		$_locals 	= array();
		
		for($i = 0;$i < $tcnt;$i++) {
			switch($et[$i][0]) {
				case T_CURLY_CLOSE:
					if($switch) $switch = false;
					break;
				case T_PARENTHESIS_OPEN:
					if($args === false) {
						$args = $this->parse_args($i);
					}
					break;
				case T_VARIABLE:
					if($et[$i][1] == '$this') {
						$j = $this->find($i, T_STRING, 3);
						if($j !== false) {
							$this->add_parent_data($et[$j][1], T_VARIABLE);
							$i = $j;
						}
					} else {
						$_locals[] = $et[$i][1];
					}
					break;
				case T_SEMICOLON;
					if(isset($abstract))
						break 2;
					break;
				case T_BACKTICK:
					$pos = $et[$i];
					while(true) {
						$t = $et[++$i];
						if($t[0] == T_BACKTICK) break;
						if(in_array($t[1], array('$_REQUEST','$_POST','$_GET'))) {
							$this->report('SEC_ERROR_REQUEST', $et[$pos][1]);
						}
					}
					break;
				case T_STRING:
					$this->parse_string($i);
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
			if($_ > $this->conf[$k]['compare'])
				$this->report($k, $_);
		if(!$this->element->abstract)
			$this->process_args($locals, $args);
		$this->process_locals($locals, $_locals, $args);
	}
}