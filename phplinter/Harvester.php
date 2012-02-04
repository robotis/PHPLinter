<?php
/**
----------------------------------------------------------------------+
*  @desc			Harvester
*  @copyright 		Copyright 2012, RHÍ
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
	public function harvest($element) {
		return $this->harvest_element($element);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Harvest documentation. 
	* @param	Element Object
	* @return   Array
	----------------------------------------------------------------------+
	*/
	protected function harvest_element($element, $namespace=null) {
		$out = array();
		if(isset($element->name) && $element->type != T_ANON_FUNCTION) {
			$out = array(
				'name' => $element->name,
				'file' => realpath($element->file),
				'type' => Tokenizer::token_name($element->type),
				'comment' => $this->harvest_comment($element),
				'static' => $element->static,
				'abstract' => $element->abstract,
				'visibility' => $element->visibility,
				'namespace' => $namespace,
				'inherits' => $element->inherits,
				'implements' => $element->implements,
				'arguments' => $element->arguments
			);
			if($element->namespace) {
				$namespace = $element->namespace;
				$out['namespace'] = $namespace;
			}
			if(!empty($element->score)) {
				$out['score'] = $element->score;
			}
			if(!empty($element->elements)) {
				$nodes = array();
				foreach($element->elements as $_) {
					$nodes[] = $this->harvest_element($_, $namespace);
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
	protected function harvest_comment($element) {
		$comment = array();
		if(!empty($element->comments)) {
			foreach($element->comments as $_) {
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