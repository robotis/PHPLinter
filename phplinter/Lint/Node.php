<?php
namespace phplinter\Lint;
class Node {
	/**
	----------------------------------------------------------------------+
	* @desc 	Create blank node
	----------------------------------------------------------------------+
	*/
	public function __construct() {
		$this->visibility 	= null;
		$this->abstract 	= null;
		$this->static 		= null;
		$this->extends 		= null;
		$this->implements 	= null;
		$this->namespace 	= null;
		$this->arguments 	= null;
		$this->type 		= null;
		$this->file	 		= null;
		$this->parent		= null;
		$this->name 		= null;
		$this->owner 		= null;
		$this->depth 		= null;
		$this->end_line 	= null;
		$this->length 		= null;
		$this->token_count 	= null;
		
		$this->comments 	= array();
		$this->tokens 		= array();
		$this->nodes 		= array();
	}
}