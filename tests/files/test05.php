<?php
/**
----------------------------------------------------------------------+
*  @desc			Test old stuff
*  @flag	W1	L14
*  @flag	W2	L15
*  @flag	E7	L15
*  @flag	E7	L16
*  @flag	C4	L22
*  @flag	W17	L28
*  @score	7.09
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
