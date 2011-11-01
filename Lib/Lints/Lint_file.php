<?php
namespace PHPLinter;
class Lint_file extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return 	FIXME
	----------------------------------------------------------------------+
	*/
	public function _lint() {
//		echo "Lint_file::lint - {$this->element->name}\n";
		$fp = explode('/', $this->element->file);
		$pretty_file_name = $fp[count($fp)-1];
		$this->element->parent = $pretty_file_name;
		
		$lnum = 1;
		foreach(file($this->element->file) as $_) {
			$len = mb_strlen($_);
			if($len > $this->conf['CON_LINE_LENGTH']['compare']) {
				$this->report('CON_LINE_LENGTH', $len, $lnum);
			}
			$lnum++;
		}
//		$this->element->start_line = 1;
		
		$tcnt = $this->element->token_count;
		$et = $this->element->tokens;
		for($i = 0;$i < $tcnt;$i++) {
//			echo Tokenizer::token_name($et[$i][0]) . "\n";
			switch($et[$i][0]) {
				case T_CLOSE_TAG:
					if($this->find($i, T_OPEN_TAG, null) === false) {
						if(count($et) - $i > 1)
							if($this->next($i))
								$this->report('REF_HTML_AFTER_CLOSE', null, $et[$i][2]);
							else
								$this->report('WAR_WS_AFTER_CLOSE', null, $et[$i][2]);
					} else {
						$this->common_tokens($i);
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
		return $this->reports;
	}
}