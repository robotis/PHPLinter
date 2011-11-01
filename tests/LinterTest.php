<?php
/**
----------------------------------------------------------------------+
*  @desc			PHPLinter unittest
*  @copyright 		Copyright 2010
*  @file 			LinterTest.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    Jun 11, 2010
*  @package 		PHPLinter
----------------------------------------------------------------------+
*/
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Extensions/PhptTestSuite.php';
define('UNITTEST', true);

require dirname(__FILE__) . '/../Lib/Linter.php';

class LinterTest extends PHPUnit_Framework_TestCase {
	/**
	----------------------------------------------------------------------+
	* @desc 	Test all phplinter files individually.
	----------------------------------------------------------------------+
	*/
	public function test_self_indiv() {
		$dir = dirname(__FILE__) . '/../Lib/';
		foreach(array(
			$dir . 'Linter.php',
			$dir . '/../phplinter',
			$dir . 'Tokenizer.php',
			$dir . 'Set.php',
			$dir . 'Path.php',
			$dir . 'Report.php',
			$dir . 'constants.php',
			dirname(__FILE__) . '/LinterTest.php',
			) as $_) {
			$ll = new PHPLinter($_);
			$report = $ll->lint();
			//$this->assertEquals(0.0, $ll->penalty(), $_);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test01.php. Emptys
	----------------------------------------------------------------------+
	*/
	public function test_empty() {
		$ll = new PHPLinter(dirname(__FILE__) . '/files/test01.php');
		$report = $ll->lint();
		$this->assertEquals(9.10, $ll->score());
		$this->assertEquals(3, count($report));
		$this->assertEquals($report[0]['flag'], 'W10');
		$this->assertEquals($report[1]['flag'], 'W11');
		$this->assertEquals($report[2]['flag'], 'W12');
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test02.php. Uncommented
	----------------------------------------------------------------------+
	*/
	public function test_uncommented() {
		$ll = new PHPLinter(dirname(__FILE__) . '/files/test02.php');
		$report = $ll->lint();
		$this->assertEquals(6, count($report));
		$this->assertEquals($report[0]['flag'], 'E2');
		$this->assertEquals($report[1]['flag'], 'E3');
		$this->assertEquals($report[2]['flag'], 'E4');
		$this->assertEquals($report[3]['flag'], 'E5');
		$this->assertEquals($report[4]['flag'], 'E6');
		$this->assertEquals($report[5]['flag'], 'E4');
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test03.php. Unused args/vars
	----------------------------------------------------------------------+
	*/
	public function test_unused() {
		$ll = new PHPLinter(dirname(__FILE__) . '/files/test03.php');
		$report = $ll->lint();
		$this->assertEquals(2, count($report));
		$this->assertEquals($report[0]['flag'], 'W4');
		$this->assertEquals($report[1]['flag'], 'W3');
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test04.php. Conventions
	----------------------------------------------------------------------+
	*/
	public function test_convention() {
		$ll = new PHPLinter(dirname(__FILE__) . '/files/test04.php');
		$report = $ll->lint();
		$this->assertEquals(6, count($report));
		$this->assertEquals($report[0]['flag'], 'C2');
		$this->assertEquals($report[1]['flag'], 'W4');
		$this->assertEquals($report[2]['flag'], 'C4');
		$this->assertEquals($report[3]['flag'], 'C1');
		$this->assertEquals($report[4]['flag'], 'C3');
		$this->assertEquals($report[5]['flag'], 'C5');
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test05.php. Old stuff
	----------------------------------------------------------------------+
	*/
	public function test_old() {
		$ll = new PHPLinter(dirname(__FILE__) . '/files/test05.php');
		$report = $ll->lint();
		$this->assertEquals(4, count($report));
		$this->assertEquals($report[0]['flag'], 'W1');
		$this->assertEquals($report[1]['flag'], 'W2');
		$this->assertEquals($report[2]['flag'], 'C4');
		$this->assertEquals($report[3]['flag'], 'W16');
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test06.php. HTML Mixins
	----------------------------------------------------------------------+
	*/
	public function test_htmlmixin() {
		$ll = new PHPLinter(dirname(__FILE__) . '/files/test06.php');
		$report = $ll->lint();
		$this->assertEquals(4, count($report));
		$this->assertEquals($report[0]['flag'], 'R8');
		$this->assertEquals($report[1]['flag'], 'R8');
		$this->assertEquals($report[2]['flag'], 'R8');
		$this->assertEquals($report[3]['flag'], 'R9');
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Test09.php. Security
	----------------------------------------------------------------------+
	*/
	public function test_security() {
		$ll = new PHPLinter(dirname(__FILE__) . '/files/test09.php');
		$report = $ll->lint();
		$this->assertEquals(4, count($report));
		$this->assertEquals($report[0]['flag'], 'S2');
		$this->assertEquals($report[1]['flag'], 'S1');
		$this->assertEquals($report[2]['flag'], 'S3');
		$this->assertEquals($report[3]['flag'], 'S1');
	}
}