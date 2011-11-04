<?php
// @flag	E3	L9
// @flag	E7	L10
// @flag	E4	L11
// @flag	E5	L16
// @flag	E6	L17
// @flag	E4	L18
// @score   4.00
class Uncommented{
	protected $uncommented;
	public function no_comment_method(){$this->uncommented=false;}
	/*
	 * TEST COMMENT
	 * */
}
function no_comment_function(){;;/*NON EMPTY*/}
interface Uncommented_int{
	public function no_comment_method_int();
}
