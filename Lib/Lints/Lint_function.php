<?php
/**
----------------------------------------------------------------------+
*  @desc			FIXME
----------------------------------------------------------------------+
*  @copyright 		Copyright 2011, RHÍ
*  @file 			Lint_function.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    Oct 29, 2011
*  @package 		FIXME
----------------------------------------------------------------------+
*/
namespace PHPLinter;
class Lint_function extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function _lint() {
//		echo "Lint_function::lint - {$this->element->name}\n";
		$tcnt 		= count($this->element->tokens);
		$et 		= $this->element->tokens;
		$args		= false;
		$_locals 	= array();
		$branches 	= 0;
		$abstract   = false;
		
		if($this->element->empty) 
			$this->report('WAR_EMPTY_FUNCTION');
			
		if(empty($this->element->comments))
			$this->report('ERR_NO_DOCHEAD_FUNCTION');
			
		$regex = $this->conf['CON_FUNCTION_NAME']['compare'];
		if(!(substr($this->element->name, 0, 2) == '__') 
			&& !preg_match($regex, $this->element->name))
			$this->report('CON_FUNCTION_NAME', $regex);
			
		for($i = 0;$i < $tcnt;$i++) {
//			echo Tokenizer::token_name($et[$i][0]) . "\n";
			switch($et[$i][0]) {
				case T_ABSTRACT:
					$abstract = true;
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
					$_locals[] = $et[$i][1];
					break;
				case T_SEMICOLON;
					if(isset($abstract))
						break 2;
					break;
				case T_BACKTICK:
					$pos = $et[$i];
					while(true) {
						$t = $this->element->tokens[++$i];
						if($t[0] == T_BACKTICK) break;
						if(in_array($t[1], array('$_REQUEST','$_POST','$_GET'))) {
							$this->report('SEC_ERROR_REQUEST', $this->element->tokens[$pos][1]);
						}
					}
					break;
				default:
					$this->common_tokens($i);
					break;
			}
		}
		if(empty($visibility) && $this->element->type == T_METHOD)
			$this->report('CON_NO_VISIBILITY');
		$locals = array_unique($_locals);
		$compares = array(
			'REF_ARGUMENTS' => count($args),
			'REF_LOCALS' => count($locals),
			'REF_BRANCHES' => $branches,
			'REF_FUNCTION_LENGTH' => $this->element->length
		);
		foreach($compares as $k => $_)
			if($_ > $this->conf[$k]['compare'])
				$this->report($k, $_);
				
		if(!$this->element->abstract)
			$this->process_args($locals, $args);
			
		$this->process_locals($locals, $_locals, $args);
		return $this->reports;
	}
}

