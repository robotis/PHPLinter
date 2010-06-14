<?php
/**
----------------------------------------------------------------------+
*  @desc			Test old stuff
*  @file 			test01.php
*  @package 		tests
----------------------------------------------------------------------+
*/
class TEST{
	var $oldvar;
	private $newvar;
	/**
	----------------------------------------------------------------------+
	* @desc 	Old style ctor
	----------------------------------------------------------------------+
	*/
	public function TEST() {
		$this->newvar = 'used';
		$this->oldvar = 'used';
	}
}
// Whitespace after close tag
?>
