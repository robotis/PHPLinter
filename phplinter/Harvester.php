<?php
/**
----------------------------------------------------------------------+
*  @desc			Harvester
*  @copyright 		Copyright 2012
*  @file 			Harvester.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    Feb 2, 2012
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
namespace PHPLinter;
class Harvester {
	/**
	----------------------------------------------------------------------+
	* @desc 	Harvest documentation
	* @param	Element Object
	* @return   Array
	----------------------------------------------------------------------+
	*/
	public function harvest($node) {
		return $this->harvest_node($node);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Harvest documentation. 
	* @param	Element Object
	* @return   Array
	----------------------------------------------------------------------+
	*/
	protected function harvest_node($node, $namespace=null) {
		$out = array();
		if(isset($node->name) && $node->type != T_ANON_FUNCTION) {
			$out = array(
				'name' 			=> $node->name,
				'file' 			=> realpath($node->file),
				'type' 			=> Tokenizer::token_name($node->type),
				'comment' 		=> $this->harvest_comment($node),
				'static' 		=> $node->static,
				'abstract' 		=> $node->abstract,
				'visibility' 	=> $node->visibility,
				'namespace' 	=> $namespace,
				'inherits' 		=> $node->inherits,
				'implements' 	=> $node->implements,
				'arguments' 	=> $node->arguments
			);
			if($node->namespace) {
				$namespace = $node->namespace;
				$out['namespace'] = $namespace;
			}
			if(!empty($node->score)) {
				$out['score'] = $node->score;
			}
			if(!empty($node->nodes)) {
				$nodes = array();
				foreach($node->nodes as $_) {
					$nodes[] = $this->harvest_node($_, $namespace);
				}
				if($nodes) {
					$out['nodes'] = $nodes;
				}
			}
		} 
		return $out;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Harvest docs
	* @param	Element Object
	* @return   Array
	----------------------------------------------------------------------+
	*/
	protected function harvest_comment($node) {
		$comment = array();
		if(!empty($node->comments)) {
			foreach($node->comments as $_) {
				if($_->type === T_DOC_COMMENT) {
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
						$s = preg_replace('/\+?(\-\-+|~~+|\*\*+)\+?/', '', $s);
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
		}
		return $comment;
	}
}