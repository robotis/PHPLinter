<?php
/**
----------------------------------------------------------------------+
* 	@desc 	Test
*	@flag	W4	L21
*	@flag	W4	L21
*	@flag	I2  L24
*	@flag	W3	L25
*	@flag	W3	L25
*	@flag	W3	L25
*	@flag 	W3	L25
*	@flag	W3	L25
*	@flag	W3	L25
*	@flag	W3	L25
*	@flag	W3	L25
*	@flag	R6	L25
*	@flag	R13	L25
*	@score	5.40
----------------------------------------------------------------------+
*/
function test() {
	$_locals = array();
	$ll = '';
	$cnt = count(array_filter($_locals, 
				  			  function($s, $l1, $l2, $l3, $l4, $l5, $l6, $l7) 
							  use($ll, $l1, $l2, $l3, $l4, $l5, $l6, $l7) {
		$s = array_unique($s);
		return $s;
	}));
	$_locals = array_unique($_locals);
	return function() {
		echo "TEST";
	};
}
