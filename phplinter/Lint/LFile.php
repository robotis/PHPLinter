<?php
/**
----------------------------------------------------------------------+
*  @desc			Lint a file.
----------------------------------------------------------------------+
*  @file 			Lint_file.php
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
namespace phplinter\Lint;
class LFile extends BaseLint implements ILint {
	/**
	----------------------------------------------------------------------+
	* @desc 	__construct
	* @param	Object 	Node Object
	* @param	Array	Rule set
	* @param	Int		Option flags
	----------------------------------------------------------------------+
	*/
	public function __construct($node, $rules, $options=0) {
		parent::__construct($node, $rules, $options);
		// File scope at 0
		$this->scope	= 0;
		$this->branches	= 0;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Analyze file
	----------------------------------------------------------------------+
	*/
	public function _lint() {
		$fp = explode('/', $this->node->file);
		$pretty_file_name = $fp[count($fp)-1];
		$this->node->parent = $pretty_file_name;
		
		$this->plines();
		
		$tcnt = $this->node->token_count;
		$et = $this->node->tokens;
		$open 		= false;
		$classes 	= 0;
		$functions 	= 0;
		$globals 	= array();
		for($i = 0;$i < $tcnt;$i++) {
			switch($et[$i][0]) {
				case T_NAMESPACE:
					$i = $this->pnamespace($i);
					break;
				case T_INLINE_HTML:
					if($open === true) 
						$this->common_tokens($i);
					break;
				case T_CLOSE_TAG:
					if($this->pclosetag($i))
						break 2;
					break;
				case T_OPEN_TAG:
					if($open === false) {
						$this->popentag($i);
						$open = true;
					}
					break;
				case T_CLASS:
					$classes++;
					break;
				case T_FUNCTION:
					$functions++;
					break;
				case T_STRING:
					if($et[$i][1] === 'define') {
						$p = $this->find($i+1, T_CONSTANT_ENCAPSED_STRING);
						$this->node->constants[] = $et[$p][1];
						$i = $p;
					}
					break;
				case T_VARIABLE:
					$globals[] = $et[$i][1];
					break;
				default:
					$this->common_tokens($i);
					break;
			}
		}
		$globals = array_unique($globals);
		$compares = array(
			'REF_BRANCHES' => $this->branches,
			'CON_FILE_CLASSES' => $classes,
			'REF_FILE_FUNCTIONS' => $functions,
			'REF_FILE_GLOBALS' => count($globals)
		);
		foreach($compares as $k => $_)
			if($_ > $this->rules[$k]['compare'])
				$this->report($k, $_);
			
		return $this->reports;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Gather namespace
	* @param	int		start
	* @return   int		stop
	----------------------------------------------------------------------+
	*/
	protected function pnamespace($i) {
   		$et = $this->node->tokens;
      	$ns = array();
     	while($et[++$i][0] != T_SEMICOLON) {
       		$ns[] = $et[$i][1];
       	}
     	$this->node->namespace = implode('', $ns);
    	return $i;       
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process file lines
	----------------------------------------------------------------------+
	*/
	protected function plines() {
		$lnum = 1;
		if($this->report_on('C') || $this->report_on('F')) {
			foreach(file($this->node->file) as $_) {
				$len = mb_strlen($_);
				if($len > $this->rules['CON_LINE_LENGTH']['compare']) {
					$this->report('CON_LINE_LENGTH', $len, $lnum);
				}
				if($this->report_on('F') && preg_match('/^(\s+)[^\s]+$/u', $_, $m)) {
					$t = trim($m[1], "\t");
					$s = trim($m[1], " ");
					if($t && $s) {
						$this->report('FMT_MIXED_SPACES_TABS', null, $lnum);
					}
				}
				$lnum++;
			}
			if($lnum > $this->rules['REF_FILE_LENGTH']['compare']) {
				$this->report('REF_FILE_LENGTH', $lnum);
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Process open tag
	----------------------------------------------------------------------+
	*/
	protected function popentag($pos) {
		$o = $this->node->tokens;
		if($pos > 0) {
			$k = $this->find(-1, T_INLINE_HTML, $pos);
			if($k !== false)
				$this->report('REF_HTML_BEFORE_OPEN', null, $o[$k][2]);
			else
				$this->report('WAR_WS_BEFORE_OPEN', null, $o[$pos-1][2]);
		}
	}	
	/**
	----------------------------------------------------------------------+
	* @desc 	Process close tag
	----------------------------------------------------------------------+
	*/
	protected function pclosetag($pos) {
		$o = $this->node->tokens;
		if($this->find($pos, T_OPEN_TAG, null) === false) {
			if(($this->node->token_count - $pos) > 1) {
				if($this->next($pos) !== false)
					$this->report('REF_HTML_AFTER_CLOSE', null, $o[$pos][2]);
//				else
//					$this->report('WAR_WS_AFTER_CLOSE', null, $o[$pos][2]);
				return true;
			}
		} 
		return false;
	}	
}