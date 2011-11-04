<?php
// @flag	D2	L9
// @flag	D6	L10
// @flag	D3	L11
// @flag	D4	L16
// @flag	D5	L17
// @flag	D3	L18
// @score   8.2
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
