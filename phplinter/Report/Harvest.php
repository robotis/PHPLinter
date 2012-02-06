<?php
/**
----------------------------------------------------------------------+
*  @desc			FIXME
*  @copyright 		Copyright 2012
*  @file 			Harvest.php
*  @author 			Jóhann T. Maríusson <jtm@robot.is>
*  @since 		    Feb 6, 2012
*  @package 		phplinter
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
		echo json_encode($this->out);
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