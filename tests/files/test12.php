<?php
/**
----------------------------------------------------------------------+
*  @desc	Test metrics
*  @rule	REF_CLASS_METHODS	C5
*  @rule	REF_CLASS_PROPERTYS	C5
*  @rule	REF_FILE_GLOBALS	C5
*  @rule	REF_FILE_FUNCTIONS	C5
*  @flag	R15 L1
*  @flag	R18 L1
*  @flag	R16 L16
*  @flag	R17	L16
*  @score	6.80
----------------------------------------------------------------------+
*/
class Test {
	/* @var int */
	private $v1;
	/* @var int */
	private $v2;
	/* @var int */
	private $v3;
	/* @var int */
	private $v4;
	/* @var int */
	private $v5;
	/* @var int */
	private $v6;
	/**
	 * TEST
	 */
	public function mt1() {$this->v1 = 1;}
	/**
	 * TEST
	 */
	public function mt2() {$this->v2 = 1;}
	/**
	 * TEST
	 */
	public function mt3() {$this->v3 = 1;}
	/**
	 * TEST
	 */
	public function mt4() {$this->v4 = 1;}
	/**
	 * TEST
	 */
	public function mt5() {$this->v5 = 1;}
	/**
	 * TEST
	 */
	public function mt6() {$this->v6 = 1;}
}
$v1 = $v2 = $v3 = $v4 = $v5 = $v6;
/**
 * TEST
 */
function mt1() {;/* NON EMPTY */}
/**
 * TEST
 */
function mt2() {;/* NON EMPTY */}
/**
 * TEST
 */
function mt3() {;/* NON EMPTY */}
/**
 * TEST
 */
function mt4() {;/* NON EMPTY */}
/**
 * TEST
 */
function mt5() {;/* NON EMPTY */}
/**
 * TEST
 */
function mt6() {;/* NON EMPTY */}
