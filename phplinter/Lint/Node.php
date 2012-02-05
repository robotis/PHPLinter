<?php
namespace phplinter\Lint;
class Node {
	/**
	----------------------------------------------------------------------+
	* @desc 	Create blank node
	----------------------------------------------------------------------+
	*/
	public function __construct() {
		$this->visibility = false;
		$this->abstract = false;
		$this->static = false;
	}
}