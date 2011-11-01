<?php
namespace PHPLinter;
class Lint_comment extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function lint() {
//		echo "Lint_comment::lint - {$this->element->name}\n";
		$tcnt = count($this->element->tokens);
		$empty = true;
		for($i = 0;$i < $tcnt;$i++) {
			$comment = $this->element->tokens[$i][1];
			if($this->element->tokens[$i] === T_NEWLINE)
				continue;
			if(preg_match('/[^\s\/\*]+/u', $comment)) {
				$empty = false;
				if(preg_match('/(FIXME|TODO)/i', $comment, $m)) {
					$this->report('INF_UNDONE', $m[1]);
				}
				if(preg_match('/(HACK)/i', $comment, $m)) {
					$this->report('WAR_HACK_MARKED');
				}
				if(preg_match($this->conf['CON_WS_COMMENTED_CODE']['compare'], 
				              $comment, $m)) {
					$this->report('CON_WS_COMMENTED_CODE');
				}
			}
		}
		if($empty) $this->report('CON_EMPTY_COMMENT');
		return $this->reports;
	}
}