<?php
/**
----------------------------------------------------------------------+
* @desc 	TEST
* @flag		R11	L24
* @flag		R13	L57
* @flag		R13	L79
* @flag		R4	L89
* @flag		R13	L102
* @score	6.00
----------------------------------------------------------------------+
*/
class Test {
	/**
	----------------------------------------------------------------------+
	* @desc 	Nested switch
	----------------------------------------------------------------------+
	*/
	public function test($var) {
		switch($var) {
			case 'X':
				if(true) {
					// Mark first instance of nested switch
					switch($var) {
						case 1:
							break;
					}
					return $var;
				}
				break;
			case 'Y':
				// No need to mark
				switch($var) {
					case 1:
						break;
				}
			default:
				break;
		}
		switch($var) {
			case 'X':
				break;
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test nesting
	----------------------------------------------------------------------+
	*/
	public function test_nest() {
		if(true) {
			if(true) {
				if(true) {
					if(true) {
						if(true) {
							if(true) {
								if(true) {
	
								}
							}
						}
					}
				}
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	No angel brakets
	----------------------------------------------------------------------+
	*/
	public function test_no_braket() {
		if(true) 
			if(true) 
				if(true) 
					if(true) 
						if(true) 
							if(true) 
								if(true) 
									return true;
		if(true) return false;
					
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test stupid indentation
	----------------------------------------------------------------------+
	*/
	public function test_no_bad() {
		if(true)
		$var = 10;
		if(true)
		$var = 10;
		if(true)
		$var = 10;
		if(true)
		if(true)
		if(true)
		if(true)
		if(true)
		if(true)
		if(true)
		$var = 10;
		if(true)
		$var = 10;
		if(true)
		$var = 10;
		if(true)
		$var = 10;
		if(true)
		$var = 10;
		if(true)
		$var = 10;
		if(true)
		$var = 10;
		return $var;
	}
}