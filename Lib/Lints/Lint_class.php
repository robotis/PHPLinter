<?php
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
		
		if(empty($this->element->comments))
			$this->report('ERR_NO_DOCHEAD_CLASS');
			
		$regex = $this->conf['CON_CLASS_NAME']['compare'];
		if(!preg_match($regex, $this->element->name))
			$this->report('CON_CLASS_NAME', $regex);
			
			
		
		$tcnt = count($this->element->tokens);
		$locals = array();
		$methods = 0;
		$comment = false;
		for($i = 0;$i < $tcnt;$i++) {
			$token = $this->element->tokens[$i];
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
				case T_METHOD:
					$comment = false;
					$methods++;
					break;
				default:
					$this->common_tokens($i);
					break;
			}
		}
		$len = $this->element->length;
		if($len > $this->conf['REF_CLASS_LENGTH']['compare'])
			$this->report('REF_CLASS_LENGTH', $len);	
		
		if(!empty($this->locals[T_METHOD]) && 
			in_array($this->element->name, $this->locals[T_METHOD]))
			$this->report('WAR_OLD_STYLE_CONSTRUCT');
		
		if(empty($this->locals[T_VARIABLE])) 
			$this->locals[T_VARIABLE] = array();
		$vars = array_diff($locals, $this->locals[T_VARIABLE]);
		foreach($vars as $_) {
			$this->report('WAR_UNUSED_PROPERTY', $_);	
		}
		
		return $this->reports;
	}
}