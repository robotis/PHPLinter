<?php
/**
----------------------------------------------------------------------+
* @desc 	Test
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