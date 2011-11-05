<?php
/**
----------------------------------------------------------------------+
* @desc 	TEST
* @flag		R11	L26
* @flag		R14	L59
* @flag		R14	L81
* @flag		R4	L91
* @flag		R14	L104
* @flag		R14	L132
* @flag		I2	L144
* @score	5.20
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
	/**
	----------------------------------------------------------------------+
	* @desc 	Test
	----------------------------------------------------------------------+
	*/
	public function test_for() {
		for($i =0; $i<3;$i++) {
			if(true) 
				if(true) 
					if(true) 
						if(true) 
							if(true)
								if(true)
									// Too deep
									return false;
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Scope test
	----------------------------------------------------------------------+
	*/
	public function test_scoping($dirs) {
		if($dirs) {
			uksort($dirs, function($a, $b) {
				return strtolower($a) > strtolower($b);
			});
		}
		for($i =0; $i<3;$i++)
			return $i;
		return $dirs;
	}
}