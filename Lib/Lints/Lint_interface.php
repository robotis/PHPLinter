<?php
namespace PHPLinter;
class Lint_interface extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function _lint() {
		$regex = $this->conf['CON_INTERFACE_NAME']['compare'];
		if(!(substr($this->element->name, 0, 2) == '__') 
			&& !preg_match($regex, $this->element->name))
			$this->report('CON_INTERFACE_NAME', $regex);
			
		if($this->element->empty) 
			$this->report('WAR_EMPTY_INTERFACE');
		
		if(empty($this->element->comments))
			$this->report('ERR_NO_DOCHEAD_INTERFACE');
			
		$len = $this->element->length;
		if($len > $this->conf['REF_CLASS_LENGTH']['compare'])
			$this->report('REF_CLASS_LENGTH', $len);	
			
		$regex = $this->conf['CON_CLASS_NAME']['compare'];
		if(!preg_match($regex, $this->element->name))
			$this->report('CON_INTERFACE_NAME', $regex);
			
		$tcnt = count($this->element->tokens);
		$et = $this->element->tokens;
		$locals 	= array();
		for($i = 0;$i < $tcnt;$i++) {
			switch($et[$i][0]) {
				case T_PUBLIC:
				case T_PRIVATE:
				case T_PROTECTED:
					break;
				default:
					$this->common_tokens($i);
					break;
			}
		}
		
		if(!empty($this->locals[T_METHOD]) && 
			in_array($this->element->name, $this->locals[T_METHOD]))
			$this->report('WAR_OLD_STYLE_CONSTRUCT');
		
		return $this->reports;
	}
}