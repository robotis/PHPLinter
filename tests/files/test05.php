
<?php
/**
----------------------------------------------------------------------+
*  @desc			Test old stuff
*  @flag	W19	L1
*  @flag	W1	L16
*  @flag	W2	L17
*  @flag	D6	L17
*  @flag	D6	L18
*  @flag	C4	L24
*  @flag	W17	L33
*  @score	8.19
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
