<?php
/**
----------------------------------------------------------------------+
*  @desc			FIXME
----------------------------------------------------------------------+
*  @copyright 		Copyright 2011, RHÍ
*  @file 			Lint_method.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    Oct 29, 2011
*  @package 		FIXME
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
		$branches 	= 0;
		
		for($i = 0;$i < $tcnt;$i++) {
			switch($et[$i][0]) {
				case T_PUBLIC:
				case T_PRIVATE:
				case T_PROTECTED:
					$this->element->visibility = true;
					break;
				case T_ABSTRACT:
					$this->element->abstract = true;
					break;
				case T_PARENTHESIS_OPEN:
					if($args === false) {
						$args = $this->parse_args($i);
					}
					break;
				case T_SWITCH:
				case T_IF:
				case T_ELSE:
				case T_ELSEIF:
					$branches++;
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
				default:
					$this->common_tokens($i);
					break;
			}
		}
		$locals = array_unique($_locals);
		$compares = array(
			'REF_ARGUMENTS' => count($args),
			'REF_LOCALS' => count($locals),
			'REF_BRANCHES' => $branches,
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