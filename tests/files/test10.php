<?php
/**
----------------------------------------------------------------------+
*  @desc			Test 09
*  @flag	R12	L9
*  @score	9.20
----------------------------------------------------------------------+
*/
class Static_mix {
	/**
	 * Non static
	 * */
	public function non_static() {
		;; // Non empty
	}
	/**
	 * Static
	 * */
	public static function is_static() {
		;; // Non empty
	}
}