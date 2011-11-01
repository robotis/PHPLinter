<?php
/**
----------------------------------------------------------------------+
*  @desc			Test unused arguments
*  @file 			test01.php
*  @package 		tests
----------------------------------------------------------------------+
*/
class Test {
	/* */
	private 
	$used_property;
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