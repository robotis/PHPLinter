<?php
/**
----------------------------------------------------------------------+
*  @desc			Test old stuff
*  @flag	W1	L14
*  @flag	W2	L15
*  @flag	D6	L15
*  @flag	D6	L16
*  @flag	C4	L22
*  @flag	W17	L28
*  @score	8.49
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
