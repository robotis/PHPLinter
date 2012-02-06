<?php
/**
----------------------------------------------------------------------+
*  @desc			Harvest documentation as JSON
*  @file 			Node.php
*  @author 			Jóhann T. Maríusson <jtm@robot.is>
*  @since 		    Feb 6, 2012
*  @package 		phplinter
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
namespace phplinter\Report;
class Harvest extends Base {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function create($report, $penaltys=null, $root=null) {
		$this->penaltys = $penaltys;
		$this->root = $root; 
		if(!is_array($report)) {
			$report = array($report);
		} 
		$this->_harvest($report);
		$this->_out();
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return   FIXME
	----------------------------------------------------------------------+
	*/
	protected function _out() {
		$conf = $this->config->check('harvest');
		if($conf['out']) {
			if(is_dir($conf['out'])) {
				$file = rtrim($conf['out'], '/') . '/' . 'phplinter.harvest.json';
			} else {
				$file = $conf['out'];
			}
			$this->write($file, json_encode($this->out));
		} else {
			echo json_encode($this->out);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return   FIXME
	----------------------------------------------------------------------+
	*/
	protected function _harvest($nodes) {
		$this->namespace = null;
		$this->out = array();
		foreach($nodes as $node) {
			$parts = explode('/', trim($node->file, './'));
			$name = array_pop($parts);
			
			$this->_insert($this->out, $parts, $name, array(
				'nodes' => $this->_extract($node),
				'constants' => $node->constants
			));
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return   FIXME
	----------------------------------------------------------------------+
	*/
	protected function _extract($node) {
		if(is_array($node)) {
			$out = array();
			foreach($node as $_) {
				$out[] = $this->_extract($_);
			}
			return $out;
		}
		$out = array(
			'name' 			=> $node->name,
			'type' 			=> \phplinter\Tokenizer::token_name($node->type),
			'comment' 		=> $this->_pcomments($node),
			'static' 		=> $node->static,
			'abstract' 		=> $node->abstract,
			'visibility' 	=> $node->visibility,
			'namespace' 	=> $this->namespace,
			'extends' 		=> $node->extends,
			'implements' 	=> $node->implements,
			'arguments' 	=> $node->arguments,
			'starts'		=> $node->start_line,
			'score'			=> SCORE_FULL + $this->penaltys[$node->file],
			'nodes'			=> $this->_extract($node->nodes),
		);
		if($node->namespace) {
			$this->namespace = $node->namespace;
			$out['namespace'] = $this->namespace;
		}
		return $out;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	* @return   FIXME
	----------------------------------------------------------------------+
	*/
	protected function _pcomments($node) {
		$comment = array();
		if(!empty($node->comments)) {
			foreach($node->comments as $_) {
			if($_->type !== T_DOC_COMMENT) continue;
				$val = '';
				$tag = '';
				$text = '';
				foreach($_->tokens as $t) {
					if($tag && preg_match('/\*\//', $t[1])) {
						$comment[$tag] = $val;
						$val = '';
						$tag = '';
					}
					$s = preg_replace(
						'/^[ \t]*\/\*\*+|\**\*\/|^[ \t]*\*/u', '$1', $t[1]
					);
					// remove lines
					$s = preg_replace('/[\+\*]?(\-\-\-+|~~~+|\*\*\*+)[\+\*]?/', '', $s);
					if($s && preg_match('/@([a-z]+)(.*)/ui', $s, $m)) {
						if($tag) {
							$comment[$tag] = $val;
							$val = '';
							$tag = '';
						}
						$tag = trim($m[1]);
						$val = $m[2];
					} else {
						if($tag) {
							$val .= $s;
						} else {
							$text .= $s;
						}
					}
				}
				if($tag) $comment[$tag] = $val;
				$comment['text'] = $text;
			}
		}
		return $comment;
	}
}