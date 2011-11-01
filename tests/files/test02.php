<?php
class Uncommented{
	protected $uncommented;
	public function no_comment_method(){;;/*NON EMPTY*/}
	/*
	 * TEST COMMENT
	 * */
}
function no_comment_function(){;;/*NON EMPTY*/}
interface Uncommented_int{
	public function no_comment_method_int;}
