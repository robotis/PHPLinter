<?php
/**
----------------------------------------------------------------------+
* @desc 	Unreachable code.
* @flag		W15	L23
* @flag		W15	L46
* @flag		W15	L85
* @flag		W15	L90
* @score	8.80
----------------------------------------------------------------------+
*/
class Test {
	/**
	 * TEST
	 */
	public function test_ur() {
		$var = 1;
		if($var) return true;
		$var = 2;
		// COMMENT
		return $var;
		// COMMENT
		if($var) return false;
	}
	/**
	 * TEST
	 */
	public function test_a() {
		// Linter will not evaluate expression
		if(true) {
			return true;
		}
		$no_warning = 1;
		return $no_warning;
	}
	/**
	 * TEST
	 */
	public function test_exit() {
		$no_warning = 1;
		die("
		
		Multiline string
		
		");
		return $no_warning;
	}
	/**
	 * TEST
	 */
	public function test_for() {
		for($i =0; $i<3;$i++) {
			
		}

		if (true) {
			return false;
		}
		$reachable = true;
		return $reachable;
	}
	/**
	 * TEST
	 */
	public function test_str() {
		$error = 9;
		$x = "'{$error}'";
		if(true) {
			return $x;
		}
		// Reachable
		return $x;
	}
}
/**
* TEST
*/
function test_ur() {
	$var = 1;
	if($var) return true;
	$var = 2;
	// COMMENT
	return $var;
	// COMMENT
	if($var) return false;
	// COMMENT
}
exit();
// Unreachable code here
$var = 1;
echo($var);
?>
