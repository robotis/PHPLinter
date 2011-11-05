<?php
/**
----------------------------------------------------------------------+
*  @desc			Lint a comment.
----------------------------------------------------------------------+
*  @file 			Lint_comment.php
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
class Lint_comment extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	Analyze comment
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public function lint() {
		$tcnt = count($this->element->tokens);
		$empty = true;
		for($i = 0;$i < $tcnt;$i++) {
			$comment = $this->element->tokens[$i][1];
			if($this->element->tokens[$i] === T_NEWLINE)
				continue;
			if(preg_match('/[^\s\/\*]+/u', $comment)) {
				$empty = false;
				if(preg_match('/(FIXME|TODO)/iu', $comment, $m)) {
					$this->report('INF_UNDONE', $m[1]);
				}
				if(preg_match('/(HACK)/iu', $comment, $m)) {
					$this->report('WAR_HACK_MARKED');
				}
				if(preg_match('/(WTF)/iu', $comment, $m)) {
					$this->report('INF_FOUND_WTF');
				}
				if(preg_match($this->rules['CON_WS_COMMENTED_CODE']['compare'], 
				              $comment, $m)) {
					$this->report('CON_WS_COMMENTED_CODE');
				}
			}
		}
		if($empty) $this->report('CON_EMPTY_COMMENT');
		return $this->reports;
	}
}