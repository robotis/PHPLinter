<?php
/**
----------------------------------------------------------------------+
*  @desc			Test unused arguments
*  @flag	W5	L11
*  @flag	W3	L22
*  @flag	W3	L38
*  @score	9.10
----------------------------------------------------------------------+
*/
abstract class Test {
	/* @var X */
	private 
	$used_property;
	/* @var Y */
	private $unused_property;
	/**
	----------------------------------------------------------------------+
	* @desc 	test method
	----------------------------------------------------------------------+
	*/
	public function test_method($used_argument, $unused_argument) {
		$this->used_property = $used_argument;
		$unused_local = null;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	abstract method
	----------------------------------------------------------------------+
	*/
	public abstract function testmethod($var);
}
/**
----------------------------------------------------------------------+
* @desc 	test function
----------------------------------------------------------------------+
*/
function test_function($unused_argument) {
	;// empty
}